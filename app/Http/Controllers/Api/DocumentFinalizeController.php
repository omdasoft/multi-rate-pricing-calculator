<?php

namespace App\Http\Controllers\Api;

use App\Actions\Documents\FinalizeDocumentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Support\Facades\Gate;

class DocumentFinalizeController extends Controller
{
    public function __invoke(Document $document, FinalizeDocumentAction $action): DocumentResource
    {
        Gate::authorize('update', $document);

        $document = $action->execute($document);

        return new DocumentResource($document->load('lineItems'));
    }
}
