<?php

namespace App\Actions\LineItems;

use App\Actions\Documents\RecalculateDocumentTotalsAction;
use App\Exceptions\DocumentIsFinalizedException;
use App\Models\LineItem;
use Illuminate\Support\Facades\DB;

final class DeleteLineItemAction
{
    public function __construct(
        private readonly RecalculateDocumentTotalsAction $recalculateDocumentTotals,
    ) {
    }

    public function execute(LineItem $line): void
    {
        $document = $line->document;

        if (! $document->isDraft()) {
            throw new DocumentIsFinalizedException();
        }

        DB::transaction(function () use ($line, $document) {
            $line->delete();
            $this->recalculateDocumentTotals->execute($document);
        });
    }
}
