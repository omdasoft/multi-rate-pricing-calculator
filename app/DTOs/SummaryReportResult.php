<?php

namespace App\DTOs;

final readonly class SummaryReportResult
{
    public function __construct(
        public int $documentCount,
        public int $sumGrandTotalCents,
        public int $sumTotalTaxCents,
        public int $sumTotalDiscountCents,
    ) {
    }
}
