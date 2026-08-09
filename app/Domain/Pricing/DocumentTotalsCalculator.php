<?php

namespace App\Domain\Pricing;

use App\Domain\Pricing\DTOs\DocumentTotals;
use App\Domain\Pricing\DTOs\LineItemCalculation;

/**
 * Sums already-computed line calculations into document-level totals.
 * Kept separate from LineItemCalculator so each class has one reason
 * to change (line math vs aggregation).
 *
 * @param  LineItemCalculation[]  $lines
 */
final class DocumentTotalsCalculator
{
    public function calculate(array $lines): DocumentTotals
    {
        return new DocumentTotals(
            subtotalCents: array_sum(array_map(fn ($l) => $l->subtotalCents, $lines)),
            totalDiscountCents: array_sum(array_map(fn ($l) => $l->discountAmountCents, $lines)),
            totalTaxCents: array_sum(array_map(fn ($l) => $l->taxAmountCents, $lines)),
            grandTotalCents: array_sum(array_map(fn ($l) => $l->lineTotalCents, $lines)),
        );
    }
}
