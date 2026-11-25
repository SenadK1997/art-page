<?php

namespace Database\Seeders;

use App\Models\PaintingStyle;
use Illuminate\Database\Seeder;

/**
 * The three styles and multipliers from the original homepage calculator.
 * Safe to run in production: it never overwrites a price the artist changed.
 */
class PaintingStyleSeeder extends Seeder
{
    public function run(): void
    {
        $styles = [
            ['slug' => 'apstrakcija', 'name' => 'Apstrakcija', 'multiplier' => 5, 'min_width' => 20, 'min_height' => 20, 'max_width' => 200, 'max_height' => 200, 'description' => 'Boja, tekstura i pokret — slobodne kompozicije.'],
            ['slug' => 'realizam', 'name' => 'Realizam', 'multiplier' => 10, 'min_width' => 20, 'min_height' => 20, 'max_width' => 150, 'max_height' => 150, 'description' => 'Prizori, gradovi i motivi naslikani do detalja.'],
            ['slug' => 'panorama', 'name' => 'Panorama', 'multiplier' => 15, 'min_width' => 60, 'min_height' => 30, 'max_width' => 250, 'max_height' => 150, 'description' => 'Široki prizori gradova i pejzaža.'],
        ];

        foreach ($styles as $i => $style) {
            PaintingStyle::firstOrCreate(['slug' => $style['slug']], $style + ['sort' => $i]);
        }
    }
}
