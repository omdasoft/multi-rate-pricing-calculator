<?php

namespace App\Actions\Documents;

use App\Domain\Pricing\DocumentTotalsCalculator;
use App\Domain\Pricing\DTOs\LineItemCalculation;
use App\Models\Document;

/**
 * Re-derives a document's cached totals from its (already-calculated)
 * line items. Called after every line item add/update/delete so the
 * document row never drifts from its lines.
 */
final class RecalculateDocumentTotalsAction
{
    public function __construct(
        private readonly DocumentTotalsCalculator $totalsCalculator,
    ) {
    }

    public function execute(Document $document): Document
    {
        $lineCalculations = $document->lineItems()->get()->map(
            fn ($line) => new LineItemCalculation(
                subtotalCents: $line->subtotal_cents,
                discountAmountCents: $line->discount_amount_cents,
                afterDiscountCents: $line->subtotal_cents - $line->discount_amount_cents,
                taxAmountCents: $line->tax_amount_cents,
                lineTotalCents: $line->line_total_cents,
            ),
        )->all();

        $totals = $this->totalsCalculator->calculate($lineCalculations);

        $document->update([
            'subtotal_cents' => $totals->subtotalCents,
            'total_discount_cents' => $totals->totalDiscountCents,
            'total_tax_cents' => $totals->totalTaxCents,
            'grand_total_cents' => $totals->grandTotalCents,
        ]);

        return $document;
    }
}
