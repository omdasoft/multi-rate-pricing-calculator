<?php

namespace App\Http\Controllers\Api;

use App\Actions\LineItems\AddLineItemAction;
use App\Actions\LineItems\DeleteLineItemAction;
use App\Actions\LineItems\UpdateLineItemAction;
use App\DTOs\LineItemData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLineItemRequest;
use App\Http\Requests\UpdateLineItemRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\LineItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LineItemController extends Controller
{
    public function store(StoreLineItemRequest $request, Document $document, AddLineItemAction $action): Response
    {
        Gate::authorize('update', $document);

        $action->execute($document, LineItemData::fromArray($request->validated()));
        
        return (new DocumentResource($document->fresh('lineItems')))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(
        UpdateLineItemRequest $request,
        Document $document,
        LineItem $lineItem,
        UpdateLineItemAction $action,
    ): DocumentResource {
        Gate::authorize('update', $document);
        $this->assertBelongsToDocument($document, $lineItem);

        $action->execute($lineItem, LineItemData::fromArray($request->validated()));

        return new DocumentResource($document->fresh('lineItems'));
    }

    public function destroy(Document $document, LineItem $lineItem, DeleteLineItemAction $action): Response
    {
        Gate::authorize('update', $document);
        $this->assertBelongsToDocument($document, $lineItem);

        $action->execute($lineItem);

        return response()->noContent();
    }

    private function assertBelongsToDocument(Document $document, LineItem $lineItem): void
    {
        if ($lineItem->document_id !== $document->id) {
            throw ValidationException::withMessages([
                'line_item' => 'This line item does not belong to the given document.',
            ]);
        }
    }
}
