<?php

namespace App\Actions\Documents;

use App\DTOs\DocumentData;
use App\Exceptions\DocumentIsFinalizedException;
use App\Models\Document;

final class UpdateDocumentAction
{
    public function execute(Document $document, DocumentData $data): Document
    {
        if (! $document->isDraft()) {
            throw new DocumentIsFinalizedException();
        }

        $document->update($data->toArray());

        return $document;
    }
}
