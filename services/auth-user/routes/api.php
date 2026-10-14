<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;

Route::prefix('v1')->group(function () {

    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'service' => 'auth-user',
            'status' => 'ok',
        ]);
    });

    Route::post(
        '/auth/login',
        [AuthController::class, 'login']
    );

    Route::middleware('supabase.auth')->group(function () {

        Route::get(
            '/me',
            [AuthController::class, 'me']
        );

        Route::post(
            '/auth/logout',
            [AuthController::class, 'logout']
        );

        Route::middleware(
            'permission:users.view'
        )->group(function () {
            Route::get(
                '/users',
                [UserController::class, 'index']
            );

            Route::get(
                '/users/{id}',
                [UserController::class, 'show']
            );
        });

        Route::middleware(
            'permission:users.manage'
        )->group(function () {
            Route::post(
                '/users',
                [UserController::class, 'store']
            );

            Route::patch(
                '/users/{id}',
                [UserController::class, 'update']
            );

            Route::patch(
                '/users/{id}/status',
                [UserController::class, 'updateStatus']
            );
        });

        Route::middleware(
            'permission:roles.manage'
        )->group(function () {
            Route::get(
                '/roles',
                [UserController::class, 'roles']
            );

            Route::patch(
                '/users/{id}/role',
                [UserController::class, 'updateRole']
            );
        });
    });
});
