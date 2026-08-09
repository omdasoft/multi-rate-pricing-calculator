<?php

namespace App\Actions\Documents;

use App\Exceptions\DocumentIsFinalizedException;
use App\Models\Document;

final class DeleteDocumentAction
{
    public function execute(Document $document): void
    {
        if (! $document->isDraft()) {
            throw new DocumentIsFinalizedException();
        }

        $document->delete();
    }
}
