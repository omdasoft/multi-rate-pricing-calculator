<?php

namespace App\Http\Controllers\Api;

use App\Actions\Documents\DuplicateDocumentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Support\Facades\Gate;

class DocumentDuplicateController extends Controller
{
    public function __invoke(Document $document, DuplicateDocumentAction $action): DocumentResource
    {
        Gate::authorize('view', $document);

        $copy = $action->execute($document);

        return new DocumentResource($copy->load('lineItems'));
    }
}
