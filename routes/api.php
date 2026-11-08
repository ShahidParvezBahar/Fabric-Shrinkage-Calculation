<?php

declare(strict_types=1);

use App\Http\Controllers\Api\FabricRollController;
use App\Http\Controllers\Api\ShrinkageGroupController;
use Illuminate\Support\Facades\Route;

Route::prefix('shrinkage-groups')->group(function () {
    Route::get('/', [ShrinkageGroupController::class, 'index']);
    Route::post('/preview-match', [ShrinkageGroupController::class, 'previewMatch']);
    Route::post('/import', [ShrinkageGroupController::class, 'import']);
});

Route::get('fabric-rolls/stats', [FabricRollController::class, 'stats']);
Route::apiResource('fabric-rolls', FabricRollController::class);

