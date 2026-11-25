<?php

namespace Tests\Feature;

use App\Models\PaintingStyle;
use Database\Seeders\PaintingStyleSeeder;
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
}
