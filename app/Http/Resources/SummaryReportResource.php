<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SummaryReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'document_count' => $this->documentCount,
            'sum_grand_total' => $this->sumGrandTotalCents / 100,
            'sum_total_tax' => $this->sumTotalTaxCents / 100,
            'sum_total_discount' => $this->sumTotalDiscountCents / 100,
        ];
    }
}
