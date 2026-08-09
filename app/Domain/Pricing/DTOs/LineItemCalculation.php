<?php

namespace App\Domain\Pricing\DTOs;

final readonly class LineItemCalculation
{
    public function __construct(
        public int $subtotalCents,
        public int $discountAmountCents,
        public int $afterDiscountCents,
        public int $taxAmountCents,
        public int $lineTotalCents,
    ) {
    }
}
