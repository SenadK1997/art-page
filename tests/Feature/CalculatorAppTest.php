<?php

namespace Tests\Feature;

use App\Livewire\CommissionCalculator;
use App\Models\PaintingStyle;
use App\Models\Product;
use App\Support\Settings;
use Database\Seeders\PaintingStyleSeeder;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculator_page_carries_current_prices_and_app_tags(): void
    {
        $this->seed(PaintingStyleSeeder::class);
        PaintingStyle::where('slug', 'realizam')->update(['multiplier' => 12]);

        $this->get('/kalkulator')
            ->assertOk()
            ->assertSee('<link rel="manifest" href="/kalkulator.webmanifest">', false)
            ->assertViewHas('styles', fn ($styles) => $styles->firstWhere('name', 'Realizam')['multiplier'] === 12
                && $styles->pluck('name')->contains('Panorama'))
            ->assertSee("register('/kalkulator-sw.js'", false)
            ->assertSee('<meta name="robots" content="noindex">', false);
    }

    public function test_inactive_styles_are_left_out(): void
    {
        $this->seed(PaintingStyleSeeder::class);
        PaintingStyle::where('slug', 'panorama')->update(['is_active' => false]);

        $this->get('/kalkulator')->assertViewHas('styles', fn ($styles) => ! $styles->pluck('name')->contains('Panorama'));
    }

    public function test_manifest_opens_the_app_straight_on_the_calculator(): void
    {
        $manifest = json_decode(file_get_contents(public_path('kalkulator.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('standalone', $manifest['display']);
        $this->assertStringStartsWith('/kalkulator', $manifest['start_url']);
        $this->assertSame('/kalkulator', $manifest['scope']);
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
        $this->assertFileExists(public_path('kalkulator-sw.js'));
    }
    public function test_shared_quote_link_opens_the_order_calculator_prefilled(): void
    {
        $this->seed(PaintingStyleSeeder::class);

        Livewire::withQueryParams(['stil' => 'panorama', 'w' => '120', 'h' => '60', 'ram' => 'gold'])
            ->test(CommissionCalculator::class)
            ->assertSet('initial', ['style' => 'panorama', 'width' => 120, 'height' => 60, 'frame' => 'gold']);

        // Unknown frames are ignored.
        Livewire::withQueryParams(['ram' => '<script>'])
            ->test(CommissionCalculator::class)
            ->assertSet('initial', []);

        $this->get('/slika-po-mjeri?stil=panorama&w=120&h=60')->assertOk()->assertSee('id="kalkulator"', false);
    }

    public function test_calculator_styles_carry_slug_and_image_for_the_quote(): void
    {
        $this->seed(PaintingStyleSeeder::class);

        $this->get('/kalkulator')->assertViewHas('styles', fn ($styles) => $styles->every(fn ($s) => filled($s['slug']) && array_key_exists('image', $s)));
    }

    public function test_style_without_image_borrows_one_of_its_paintings(): void
    {
        $this->seed(PaintingStyleSeeder::class);
        $style = PaintingStyle::where('slug', 'realizam')->first();
        $this->assertNull($style->imageUrl());

        $p = Product::create(['title' => 'Most', 'image' => 'paintings/most.jpg', 'painting_style_id' => $style->id]);
        $p->sizes()->create(['width' => 50, 'height' => 70, 'price' => 350]);
        $this->assertStringEndsWith('/storage/paintings/most.jpg', $style->fresh()->imageUrl());

        $style->update(['image' => 'styles/own.jpg']);
        $this->assertStringEndsWith('/storage/styles/own.jpg', $style->fresh()->imageUrl());
    }

    public function test_homepage_uses_the_image_chosen_in_settings(): void
    {
        $this->seed(PaintingStyleSeeder::class);
        app(Settings::class)->set(['hero_image' => 'site/naslovna.jpg', 'hero_caption' => 'Atelje u Sarajevu']);

        $this->get('/')
            ->assertOk()
            ->assertSee('/storage/site/naslovna.jpg', false)
            ->assertSee('Atelje u Sarajevu');
    }
}
