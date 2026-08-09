<?php

namespace App\Actions\Reports;

use App\DTOs\SummaryReportFilter;
use App\DTOs\SummaryReportResult;
use App\Models\User;

final class GenerateSummaryReportAction
{
    public function execute(User $user, SummaryReportFilter $filter): SummaryReportResult
    {
        $documents = $user->documents()
            ->whereBetween('issue_date', [$filter->fromDate, $filter->toDate])
            ->get(['grand_total_cents', 'total_tax_cents', 'total_discount_cents']);

        return new SummaryReportResult(
            documentCount: $documents->count(),
            sumGrandTotalCents: (int) $documents->sum('grand_total_cents'),
            sumTotalTaxCents: (int) $documents->sum('total_tax_cents'),
            sumTotalDiscountCents: (int) $documents->sum('total_discount_cents'),
        );
    }
}
