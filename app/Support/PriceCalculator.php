<?php

namespace App\Support;

/**
 * The original Foco-art pricing rule, kept exactly as it was:
 * price = round(width_cm × height_cm / 100 × multiplier), in KM.
 *
 * The multiplier is the price of 100 cm² (a 10 × 10 cm square) for a style:
 * Apstrakcija 5, Realizam 10, Panorama 15.
 */
class PriceCalculator
{
    public static function price(int $width, int $height, int $multiplier): int
    {
        return (int) round($width * $height / 100 * $multiplier);
    }
}
