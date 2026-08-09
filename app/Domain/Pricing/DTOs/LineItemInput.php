<?php

namespace App\Domain\Pricing\DTOs;

final readonly class LineItemInput
{
    public function __construct(
        public int $quantity,
        public int $unitPriceCents,
        public ?float $discountPercent = null,
        public ?int $discountFixedCents = null,
        public ?float $taxPercent = null,
    ) {
    }
}
