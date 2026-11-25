<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\PaintingStyle;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PaintingStyleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PaintingStyleSeeder::class);
    }

    private function painting(array $attrs): Product
    {
        $p = Product::create($attrs + ['title' => 'Gray luxury', 'painting_style_id' => PaintingStyle::first()->id]);
        $p->sizes()->create(['width' => 80, 'height' => 80, 'price' => 640]);

        return $p->fresh('sizes');
    }

    public function test_room_photo_only_has_no_wall_preview_and_no_second_frame(): void
    {
        $p = $this->painting(['room_images' => ['paintings/soba.jpg']]);

        $this->assertTrue($p->coverIsRoomPhoto());
        $this->assertStringEndsWith('/storage/paintings/soba.jpg', $p->imageUrl());
        $this->assertSame([['url' => $p->imageUrl(), 'framed' => false]], $p->photos());

        $this->get(route('gallery.show', $p))
            ->assertOk()
            ->assertDontSee('Na zidu ·', false)
            ->assertSee('/storage/paintings/soba.jpg', false);
    }

    public function test_plain_painting_photo_gets_the_wall_preview(): void
    {
        $p = $this->painting(['image' => 'paintings/slika.jpg', 'room_images' => ['paintings/soba.jpg']]);

        $this->assertFalse($p->coverIsRoomPhoto());
        $this->assertSame([true, false], array_column($p->photos(), 'framed'));

        $this->get(route('gallery.show', $p))
            ->assertOk()
            ->assertSee('Na zidu ·', false);
    }

    public function test_admin_can_save_with_only_a_room_photo_but_not_with_none(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $style = PaintingStyle::first()->id;

        Livewire::test(CreateProduct::class)
            ->fillForm(['title' => 'Bez fotografije', 'painting_style_id' => $style])
            ->set('data.sizes', [['width' => 50, 'height' => 70, 'price' => 350, 'stock' => 0]])
            ->call('create')
            ->assertHasFormErrors(['image', 'room_images']);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'title' => 'Samo soba',
                'painting_style_id' => $style,
                'room_images' => [UploadedFile::fake()->image('soba.jpg', 1200, 900)],
            ])
            ->set('data.sizes', [['width' => 50, 'height' => 70, 'price' => 350, 'stock' => 0]])
            ->call('create')
            ->assertHasNoFormErrors();

        $p = Product::where('title', 'Samo soba')->sole();
        $this->assertNull($p->image);
        $this->assertCount(1, $p->room_images);
    }
}
