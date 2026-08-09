<?php

namespace App\Domain\Pricing;

use App\Domain\Pricing\DTOs\LineItemCalculation;
use App\Domain\Pricing\DTOs\LineItemInput;
use App\Domain\Pricing\Exceptions\InvalidLineItemException;

/**
 * Pure calculation module for a single line item. No Eloquent, no I/O —
 * this is deliberate so it can be unit tested directly against the
 * assignment's sample table without a database.
 *
 * Steps (see README for the worked example):
 *   1. subtotal      = quantity x unit price
 *   2. discount      = fixed amount OR percent of subtotal (not both)
 *   3. after discount = subtotal - discount
 *   4. tax            = tax percent applied to the discounted amount
 *   5. line total     = after discount + tax
 */
final class LineItemCalculator
{
    public function calculate(LineItemInput $input): LineItemCalculation
    {
        $subtotalCents = $input->quantity * $input->unitPriceCents;

        $discountAmountCents = $this->resolveDiscount($input, $subtotalCents);

        $afterDiscountCents = $subtotalCents - $discountAmountCents;

        $taxAmountCents = $input->taxPercent !== null
            ? Money::percentOf($afterDiscountCents, $input->taxPercent)
            : 0;

        return new LineItemCalculation(
            subtotalCents: $subtotalCents,
            discountAmountCents: $discountAmountCents,
            afterDiscountCents: $afterDiscountCents,
            taxAmountCents: $taxAmountCents,
            lineTotalCents: $afterDiscountCents + $taxAmountCents,
        );
    }

    /**
     * Discount policy: a fixed discount that exceeds the line subtotal is
     * REJECTED (not clamped) — see README "Assumptions" for why.
     */
    private function resolveDiscount(LineItemInput $input, int $subtotalCents): int
    {
        if ($input->discountFixedCents !== null) {
            if ($input->discountFixedCents > $subtotalCents) {
                throw InvalidLineItemException::discountExceedsSubtotal(
                    $input->discountFixedCents,
                    $subtotalCents,
                );
            }

            return $input->discountFixedCents;
        }

        if ($input->discountPercent !== null) {
            return Money::percentOf($subtotalCents, $input->discountPercent);
        }

        return 0;
    }
}
