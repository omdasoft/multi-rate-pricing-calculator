<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'customer' => $this->customer,
            'issue_date' => $this->issue_date->toDateString(),
            'status' => $this->status->value,
            'totals' => [
                'subtotal' => $this->subtotal_cents / 100,
                'total_discount' => $this->total_discount_cents / 100,
                'total_tax' => $this->total_tax_cents / 100,
                'grand_total' => $this->grand_total_cents / 100,
            ],
            'line_items' => LineItemResource::collection($this->whenLoaded('lineItems')),
        ];
    }
}
