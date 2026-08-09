<?php

namespace App\DTOs;

final readonly class SummaryReportFilter
{
    public function __construct(
        public string $fromDate,
        public string $toDate,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fromDate: $data['from'],
            toDate: $data['to'],
        );
    }
}
