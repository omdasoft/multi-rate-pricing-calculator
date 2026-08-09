<?php

namespace App\DTOs;

final readonly class LineItemData
{
    public function __construct(
        public string $description,
        public int $quantity,
        public int $unitPriceCents,
        public ?float $discountPercent = null,
        public ?int $discountFixedCents = null,
        public ?float $taxPercent = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'],
            quantity: (int) $data['quantity'],
            unitPriceCents: (int) round(((float) $data['unit_price']) * 100),
            discountPercent: isset($data['discount_percent']) ? (float) $data['discount_percent'] : null,
            discountFixedCents: isset($data['discount_fixed'])
                ? (int) round(((float) $data['discount_fixed']) * 100)
                : null,
            taxPercent: isset($data['tax_percent']) ? (float) $data['tax_percent'] : null,
        );
    }
}
