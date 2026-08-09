<?php

namespace App\Http\Controllers\Api;

use App\Actions\Documents\CreateDocumentAction;
use App\Actions\Documents\DeleteDocumentAction;
use App\Actions\Documents\ListDocumentAction;
use App\Actions\Documents\UpdateDocumentAction;
use App\DTOs\DocumentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function index(Request $request, ListDocumentAction $action): AnonymousResourceCollection
    {
        $documents = $action->execute($request->user());
        return DocumentResource::collection($documents);
    }

    public function store(StoreDocumentRequest $request, CreateDocumentAction $action): JsonResponse
    {
        $document = $action->execute($request->user(), DocumentData::fromArray($request->validated()));

        return (new DocumentResource($document->load('lineItems')))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Document $document): DocumentResource
    {
        Gate::authorize('view', $document);

        return new DocumentResource($document->load('lineItems'));
    }

    public function update(UpdateDocumentRequest $request, Document $document, UpdateDocumentAction $action): DocumentResource
    {
        Gate::authorize('update', $document);

        $document = $action->execute($document, DocumentData::fromArray($request->validated()));

        return new DocumentResource($document->load('lineItems'));
    }

    public function destroy(Document $document, DeleteDocumentAction $action): Response
    {
        Gate::authorize('delete', $document);

        $action->execute($document);

        return response()->noContent();
    }
}
