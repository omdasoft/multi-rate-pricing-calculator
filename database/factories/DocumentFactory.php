<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'customer' => fake()->company(),
            'issue_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'status' => DocumentStatus::Draft,
        ];
    }

    public function finalized(): static
    {
        return $this->state(fn () => ['status' => DocumentStatus::Finalized, 'finalized_at' => now()]);
    }
}
