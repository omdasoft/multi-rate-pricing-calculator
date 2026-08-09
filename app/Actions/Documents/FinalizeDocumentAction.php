<?php

namespace App\Actions\Documents;

use App\Enums\DocumentStatus;
use App\Exceptions\DocumentIsFinalizedException;
use App\Models\Document;
use Illuminate\Validation\ValidationException;

final class FinalizeDocumentAction
{
    public function execute(Document $document): Document
    {
        if (! $document->isDraft()) {
            throw new DocumentIsFinalizedException();
        }

        $this->assertHasValidLines($document);

        $document->update([
            'status' => DocumentStatus::Finalized,
        ]);

        return $document;
    }

    /**
     * Stretch goal: reject finalize if any line has quantity <= 0 or a
     * negative price. Quantity is already unsigned-int-guarded and >=1
     * at write time, so this mainly guards against zero-line documents.
     */
    private function assertHasValidLines(Document $document): void
    {
        if ($document->lineItems()->count() === 0) {
            throw ValidationException::withMessages([
                'lines' => 'A document must have at least one line item before it can be finalized.',
            ]);
        }

        foreach ($document->lineItems as $line) {
            if ($line->quantity <= 0 || $line->unit_price_cents < 0) {
                throw ValidationException::withMessages([
                    'lines' => "Line \"{$line->description}\" has an invalid quantity or price.",
                ]);
            }
        }
    }
}
