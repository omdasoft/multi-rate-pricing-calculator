<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

/**
 * Stretch goal: copy a finalized (or draft) document into a brand new
 * draft, including its lines. Cached totals are copied verbatim since
 * the lines themselves are copied unchanged — no recalculation needed.
 */
final class DuplicateDocumentAction
{
    public function execute(Document $document): Document
    {
        return DB::transaction(function () use ($document) {
            $copy = $document->replicate([
                'status', 'finalized_at', 'created_at', 'updated_at',
            ]);

            $copy->status = DocumentStatus::Draft;
            
            $copy->title = "{$document->title} (copy)";

            $copy->save();

            foreach ($document->lineItems as $line) {
                $copy->lineItems()->create([
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price_cents' => $line->unit_price_cents,
                    'discount_percent' => $line->discount_percent,
                    'discount_fixed_cents' => $line->discount_fixed_cents,
                    'tax_percent' => $line->tax_percent,
                    'subtotal_cents' => $line->subtotal_cents,
                    'discount_amount_cents' => $line->discount_amount_cents,
                    'tax_amount_cents' => $line->tax_amount_cents,
                    'line_total_cents' => $line->line_total_cents,
                ]);
            }

            return $copy;
        });
    }
}
