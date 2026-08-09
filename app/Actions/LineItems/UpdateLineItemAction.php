<?php

namespace App\Actions\LineItems;

use App\Actions\Documents\RecalculateDocumentTotalsAction;
use App\Domain\Pricing\DTOs\LineItemInput;
use App\Domain\Pricing\LineItemCalculator;
use App\DTOs\LineItemData;
use App\Exceptions\DocumentIsFinalizedException;
use App\Models\LineItem;
use Illuminate\Support\Facades\DB;

final class UpdateLineItemAction
{
    public function __construct(
        private readonly LineItemCalculator $calculator,
        private readonly RecalculateDocumentTotalsAction $recalculateDocumentTotals,
    ) {
    }

    public function execute(LineItem $line, LineItemData $data): LineItem
    {
        $document = $line->document;

        if (! $document->isDraft()) {
            throw new DocumentIsFinalizedException();
        }

        return DB::transaction(function () use ($line, $data, $document) {
            $calculation = $this->calculator->calculate(new LineItemInput(
                quantity: $data->quantity,
                unitPriceCents: $data->unitPriceCents,
                discountPercent: $data->discountPercent,
                discountFixedCents: $data->discountFixedCents,
                taxPercent: $data->taxPercent,
            ));

            $line->update([
                'description' => $data->description,
                'quantity' => $data->quantity,
                'unit_price_cents' => $data->unitPriceCents,
                'discount_percent' => $data->discountPercent,
                'discount_fixed_cents' => $data->discountFixedCents,
                'tax_percent' => $data->taxPercent,
                'subtotal_cents' => $calculation->subtotalCents,
                'discount_amount_cents' => $calculation->discountAmountCents,
                'tax_amount_cents' => $calculation->taxAmountCents,
                'line_total_cents' => $calculation->lineTotalCents,
            ]);

            $this->recalculateDocumentTotals->execute($document);

            return $line;
        });
    }
}
