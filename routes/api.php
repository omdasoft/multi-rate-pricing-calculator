<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentDuplicateController;
use App\Http\Controllers\Api\DocumentFinalizeController;
use App\Http\Controllers\Api\LineItemController;
use App\Http\Controllers\Api\SummaryReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/reports/summary', SummaryReportController::class);

        Route::apiResource('documents', DocumentController::class);

        Route::post('/documents/{document}/finalize', DocumentFinalizeController::class);
        Route::post('/documents/{document}/duplicate', DocumentDuplicateController::class);

        Route::post('/documents/{document}/line-items', [LineItemController::class, 'store']);
        Route::put('/documents/{document}/line-items/{lineItem}', [LineItemController::class, 'update']);
        Route::delete('/documents/{document}/line-items/{lineItem}', [LineItemController::class, 'destroy']);
    });
});
