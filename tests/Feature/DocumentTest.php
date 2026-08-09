<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_creating_a_document_with_the_sample_lines_matches_the_expected_totals(): void
    {
        //create document
        $document = $this->createDocument();

        //add line item A
        $this->postJson("/api/v1/documents/{$document->id}/line-items", [
            'description' => 'Widget A', 
            'quantity' => 2, 
            'unit_price' => 100.00,
            'discount_percent' => 10, 
            'tax_percent' => 5,
        ]);

        //add line item B
        $this->postJson("/api/v1/documents/{$document->id}/line-items", [
            'description' => 'Widget B', 
            'quantity' => 1, 
            'unit_price' => 50.00,
            'tax_percent' => 5,
        ]);
        
        //add line item C
        $response  = $this->postJson("/api/v1/documents/{$document->id}/line-items", [
            'description' => 'Service fee', 
            'quantity' => 1, 
            'unit_price' => 200.00,
            'discount_fixed' => 20,
        ]);
        
        $totals = $response->json()['data']['totals'];

        $this->assertEquals(450.00, $totals['subtotal']);
        $this->assertEquals(40.00, $totals['total_discount']);
        $this->assertEquals(11.50, $totals['total_tax']);
        $this->assertEquals(421.50, $totals['grand_total']);
    }

    public function test_a_line_cannot_have_both_a_percent_and_fixed_discount(): void
    {
        $document = $this->createDocument();

        $response = $this->postJson("/api/v1/documents/{$document->id}/line-items", [
            'description' => 'Bad line', 
            'quantity' => 1, 
            'unit_price' => 10,
            'discount_percent' => 10, 
            'discount_fixed' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['discount_percent']);
    }

    public function test_a_fixed_discount_exceeding_the_subtotal_is_rejected(): void
    {        
        $document = $this->createDocument();

        $response = $this->postJson("/api/v1/documents/{$document->id}/line-items", [
            'description' => 'Overdiscounted', 
            'quantity' => 1, 
            'unit_price' => 10,
            'discount_fixed' => 50,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Fixed discount (5000 cents) cannot exceed the line subtotal (1000 cents).');
    }

    public function test_negative_quantity_is_rejected_with_a_specific_message(): void
    {
        $document = $this->createDocument();

        $response = $this->postJson("/api/v1/documents/{$document->id}/line-items", [
            'description' => 'Bad qty', 
            'quantity' => -1, 
            'unit_price' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');
    }

    public function test_finalizing_a_document_locks_it_from_edits(): void
    {
        $document = $this->createDocument();

        $this->addLine($document, ['description' => 'Item', 'quantity' => 1, 'unit_price' => 10]);

        $this->postJson("/api/v1/documents/{$document->id}/finalize")
            ->assertOk()
            ->assertJsonPath('data.status', 'finalized');

        $this->putJson("/api/v1/documents/{$document->id}", [
            'title' => 'Renamed', 
            'customer' => 'X', 
            'issue_date' => '2026-01-01',
        ])->assertUnprocessable();

        $this->postJson("/api/v1/documents/{$document->id}/line-items", [
            'description' => 'New line', 'quantity' => 1, 'unit_price' => 5,
        ])->assertUnprocessable();
    }

    public function test_finalizing_a_document_with_no_lines_is_rejected(): void
    {
        $document = $this->createDocument();

        $this->postJson("/api/v1/documents/{$document->id}/finalize")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('lines');
    }

    public function test_duplicating_a_finalized_document_creates_an_editable_draft_copy(): void
    {
        $document = $this->createDocument();

        $this->addLine($document, [
            'description' => 'Item', 
            'quantity' => 2, 
            'unit_price' => 25, 
            'tax_percent' => 10,
        ]);

        $this->postJson("/api/v1/documents/{$document->id}/finalize");

        $response = $this->postJson("/api/v1/documents/{$document->id}/duplicate");

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'draft');

        $this->assertNotSame($document->id, $response->json('data.id'));

        $this->assertSame(55, $response->json('data.totals.grand_total'));
    }

    public function test_a_user_cannot_view_another_users_document(): void
    {
        $other = User::factory()->create();

        $document = Document::factory()->for($other)->create();

        $this->getJson("/api/v1/documents/{$document->id}")
            ->assertForbidden();
    }

    private function createDocument(): Document
    {
        $response = $this->postJson('/api/v1/documents', [
            'title' => 'Q1 Proposal', 
            'customer' => 'Acme Co', 
            'issue_date' => '2026-02-01',
        ]);

        return Document::findOrFail($response->json()['data']['id']);
    }

    private function addLine(Document $document, array $payload)
    {
        return $this->postJson("/api/v1/documents/{$document->id}/line-items", $payload);
    }
}
