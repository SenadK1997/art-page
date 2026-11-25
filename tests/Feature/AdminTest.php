<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Order;
use App\Models\PaintingStyle;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PaintingStyleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PaintingStyleSeeder::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_guests_are_sent_to_login(): void
    {
        auth()->logout();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_pages_load(): void
    {
        $product = Product::create(['title' => 'Test', 'painting_style_id' => PaintingStyle::first()->id]);
        $product->sizes()->create(['width' => 50, 'height' => 70, 'price' => 350]);
        $order = Order::create(['full_name' => 'A B', 'phone' => '061', 'address' => 'X', 'city' => 'Y', 'total' => 350]);
        $order->items()->create(['kind' => 'painting', 'title' => 'Test', 'width' => 50, 'height' => 70, 'unit_price' => 350, 'qty' => 1]);

        foreach (['/admin', '/admin/products', '/admin/products/create', "/admin/products/{$product->slug}/edit",
            '/admin/orders', "/admin/orders/{$order->number}", '/admin/painting-styles', '/admin/tags'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_typing_a_size_fills_the_price_from_the_formula(): void
    {
        Storage::fake('public');
        $realizam = PaintingStyle::where('slug', 'realizam')->first();

        $page = Livewire::test(CreateProduct::class)
            ->fillForm([
                'title' => 'Nova slika',
                'painting_style_id' => $realizam->id,
                'image' => UploadedFile::fake()->image('slika.jpg', 800, 1000),
            ]);

        $key = array_key_first($page->get('data.sizes'));
        $page->set("data.sizes.{$key}.width", 70)
            ->set("data.sizes.{$key}.height", 100)
            ->assertSet("data.sizes.{$key}.price", 700)
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('title', 'Nova slika')->sole();
        $this->assertSame(700, $product->sizes()->sole()->price);
        $this->assertSame('nova-slika', $product->slug);
    }

    public function test_recalculate_action_fills_every_size(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('paintings/x.jpg', UploadedFile::fake()->image('x.jpg')->getContent());
        $product = Product::create(['title' => 'X', 'image' => 'paintings/x.jpg', 'painting_style_id' => PaintingStyle::where('slug', 'panorama')->first()->id]);
        $product->sizes()->createMany([
            ['width' => 50, 'height' => 70, 'price' => 1],
            ['width' => 100, 'height' => 150, 'price' => 1],
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->callAction(\Filament\Actions\Testing\TestAction::make('recalculate')->schemaComponent('sizes-section', schema: 'form'))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([525, 2250], $product->sizes()->pluck('price')->all());
    }

    public function test_standard_sizes_are_added_with_formula_prices(): void
    {
        Storage::fake('public');
        $realizam = PaintingStyle::where('slug', 'realizam')->first(); // 20–150 cm, 10 KM

        $page = Livewire::test(CreateProduct::class)
            ->fillForm([
                'title' => 'Brza slika',
                'painting_style_id' => $realizam->id,
                'image' => UploadedFile::fake()->image('slika.jpg', 800, 1000),
            ])
            ->callAction(\Filament\Actions\Testing\TestAction::make('standardSizes')->schemaComponent('sizes-section', schema: 'form'))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(
            ['30x40=120', '50x70=350', '70x100=700'],
            Product::where('title', 'Brza slika')->sole()->sizes->map(fn ($s) => "{$s->width}x{$s->height}={$s->price}")->all(),
        );
    }

    public function test_paintings_can_be_put_on_sale_from_the_list(): void
    {
        $style = PaintingStyle::first()->id;
        $a = Product::create(['title' => 'A', 'painting_style_id' => $style]);
        $b = Product::create(['title' => 'B', 'painting_style_id' => $style]);

        Livewire::test(\App\Filament\Resources\Products\Pages\ListProducts::class)
            ->callTableBulkAction('bulkSale', [$a, $b], ['discount_percent' => 20])
            ->assertHasNoTableBulkActionErrors();
        $this->assertSame([20, 20], [$a->fresh()->discount_percent, $b->fresh()->discount_percent]);

        Livewire::test(\App\Filament\Resources\Products\Pages\ListProducts::class)
            ->callTableAction('endSale', $a);
        $this->assertSame(0, $a->fresh()->discount_percent);
    }

    public function test_order_status_can_be_changed(): void
    {
        $order = Order::create(['full_name' => 'A B', 'phone' => '061', 'address' => 'X', 'city' => 'Y', 'total' => 350]);

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm(['status' => 'confirmed'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('confirmed', $order->fresh()->status);
    }
}
