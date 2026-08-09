<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LineItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price_cents / 100,
            'discount_percent' => $this->discount_percent,
            'discount_fixed' => $this->discount_fixed_cents !== null ? $this->discount_fixed_cents / 100 : null,
            'tax_percent' => $this->tax_percent,
            'subtotal' => $this->subtotal_cents / 100,
            'discount_amount' => $this->discount_amount_cents / 100,
            'tax_amount' => $this->tax_amount_cents / 100,
            'line_total' => $this->line_total_cents / 100,
        ];
    }
}
