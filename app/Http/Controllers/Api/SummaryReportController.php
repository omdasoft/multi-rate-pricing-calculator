<?php

namespace App\Http\Controllers\Api;

use App\Actions\Reports\GenerateSummaryReportAction;
use App\DTOs\SummaryReportFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\SummaryReportRequest;
use App\Http\Resources\SummaryReportResource;

class SummaryReportController extends Controller
{
    public function __invoke(SummaryReportRequest $request, GenerateSummaryReportAction $action): SummaryReportResource
    {
        $result = $action->execute($request->user(), SummaryReportFilter::fromArray($request->validated()));

        return new SummaryReportResource($result);
    }
}
