<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\LineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LineItem>
 */
class LineItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPriceCents = fake()->numberBetween(500, 20000);
        $subtotal = $quantity * $unitPriceCents;

        return [
            'document_id' => Document::factory(),
            'description' => fake()->words(3, true),
            'quantity' => $quantity,
            'unit_price_cents' => $unitPriceCents,
            'subtotal_cents' => $subtotal,
            'discount_amount_cents' => 0,
            'tax_amount_cents' => 0,
            'line_total_cents' => $subtotal,
        ];
    }
}
