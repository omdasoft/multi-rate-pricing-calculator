<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SummaryReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_report_sums_only_documents_in_range_for_the_current_user(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        Document::factory()->for($user)->create([
            'issue_date' => '2026-01-15',
            'subtotal_cents' => 10000, 'total_discount_cents' => 0,
            'total_tax_cents' => 500, 'grand_total_cents' => 10500,
        ]);

        Document::factory()->for($user)->create([
            'issue_date' => '2026-01-20',
            'subtotal_cents' => 5000, 'total_discount_cents' => 100,
            'total_tax_cents' => 250, 'grand_total_cents' => 5150,
        ]);

        // Outside the range — must not be counted.
        Document::factory()->for($user)->create([
            'issue_date' => '2026-03-01',
            'grand_total_cents' => 99999,
        ]);

        // Another user's document in range — must not be counted.
        $other = User::factory()->create();

        Document::factory()->for($other)->create([
            'issue_date' => '2026-01-16', 'grand_total_cents' => 50000,
        ]);

        $response = $this->getJson('/api/v1/reports/summary?from=2026-01-01&to=2026-01-31');

        $response->assertOk()
        ->assertJson([
            'data' => [
                'document_count' => 2,
                'sum_grand_total' => 156.50,
                'sum_total_tax' => 7.50,
                'sum_total_discount' => 1.00,
            ]
        ]);
    }

    public function test_a_to_date_before_the_from_date_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/reports/summary?from=2026-02-01&to=2026-01-01')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('to');
    }
}
