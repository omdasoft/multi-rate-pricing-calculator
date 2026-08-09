<?php

namespace App\Domain\Pricing\DTOs;

final readonly class DocumentTotals
{
    public function __construct(
        public int $subtotalCents,
        public int $totalDiscountCents,
        public int $totalTaxCents,
        public int $grandTotalCents,
    ) {
    }
}
