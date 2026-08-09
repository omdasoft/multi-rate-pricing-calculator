<?php

namespace App\Domain\Pricing;

/**
 * All monetary values in this domain are integer cents. Percentages are
 * applied against cents and rounded per-line, half up (away from zero),
 * to the nearest cent. This is the single rounding policy for the app —
 * see README "Calculation & rounding policy" for the worked example.
 */
final class Money
{
    public static function percentOf(int $cents, float $percent): int
    {
        return (int) round($cents * $percent / 100);
    }
}
