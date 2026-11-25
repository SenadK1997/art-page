<?php

namespace Tests\Unit;

use App\Support\PriceCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    /** The original homepage formula: round(w × h / 100 × multiplier). */
    #[DataProvider('prices')]
    public function test_it_keeps_the_original_formula(int $w, int $h, int $multiplier, int $expected): void
    {
        $this->assertSame($expected, PriceCalculator::price($w, $h, $multiplier));
    }

    public static function prices(): array
    {
        return [
            'apstrakcija 50×70' => [50, 70, 5, 175],
            'realizam 50×70' => [50, 70, 10, 350],
            'realizam 70×100' => [70, 100, 10, 700],
            'panorama 100×150' => [100, 150, 15, 2250],
            'rounds half up' => [33, 47, 5, 78],      // 77.55
            'rounds down' => [31, 41, 5, 64],         // 63.55 -> 64, 31*41=1271
            'small' => [20, 20, 5, 20],
        ];
    }
}
