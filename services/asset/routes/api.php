<?php

use App\Http\Controllers\Api\V1\AssetBatchController;
use App\Http\Controllers\Api\V1\AssetController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'service' => 'asset',
            'status' => 'ok',
        ]);
    });

    Route::middleware(
        'supabase.auth'
    )->group(function () {

        Route::middleware(
            'permission:assets.view'
        )->group(function () {

            Route::get(
                '/assets',
                [AssetController::class, 'index']
            );

            Route::get(
                '/assets/availability',
                [
                    AssetController::class,
                    'availability',
                ]
            );

            Route::get(
                '/assets/{id}',
                [AssetController::class, 'show']
            )->whereUuid('id');

            Route::get(
                '/asset-batches',
                [
                    AssetBatchController::class,
                    'index',
                ]
            );

            Route::get(
                '/asset-batches/{id}',
                [
                    AssetBatchController::class,
                    'show',
                ]
            )->whereUuid('id');
        });

        Route::middleware(
            'permission:assets.create'
        )->group(function () {

            Route::post(
                '/assets',
                [AssetController::class, 'store']
            );

            Route::post(
                '/assets/bulk',
                [
                    AssetController::class,
                    'bulkStore',
                ]
            );
        });

        Route::middleware(
            'permission:assets.update'
        )->patch(
            '/assets/{id}',
            [AssetController::class, 'update']
        )->whereUuid('id');

        Route::middleware(
            'permission:assets.status.update'
        )->patch(
            '/assets/{id}/status',
            [
                AssetController::class,
                'updateStatus',
            ]
        )->whereUuid('id');

        Route::middleware(
            'permission:assets.history.view'
        )->get(
            '/assets/{id}/history',
            [
                AssetController::class,
                'history',
            ]
        )->whereUuid('id');
    });
});