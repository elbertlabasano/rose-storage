<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BucketController;
use App\Http\Controllers\ObjectController;
use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

Route::prefix('backups')->middleware(['auth:sanctum', \App\Http\Middleware\BackupAdministrator::class])->group(function () {
    Route::get('/', [BackupController::class, 'index']);
    Route::post('/prepare/{type}', [BackupController::class, 'prepare'])->whereIn('type', ['zip', 'sql'])->middleware('throttle:2,1');
    Route::post('/{id}/download', [BackupController::class, 'downloadLink'])->whereUuid('id')->middleware('throttle:20,1');
});

Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('objects/{object}/public', [ObjectController::class, 'publicDownload'])
    ->name('objects.public-download');

Route::get('objects/{object}/private', [ObjectController::class, 'privateDownload'])
    ->middleware('signed')
    ->name('objects.private-download');

Route::post('auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'abilities:tokens:manage'])
    ->prefix('access-tokens')
    ->group(function () {
        Route::get('/', [AccessTokenController::class, 'index']);
        Route::post('/', [AccessTokenController::class, 'store']);
        Route::delete('/{token}', [AccessTokenController::class, 'destroy']);
    });

Route::middleware(['auth:sanctum', 'abilities:buckets:read'])->group(function () {
    Route::get('buckets', [BucketController::class, 'index']);
    Route::get('buckets/{bucket}', [BucketController::class, 'show']);
});

Route::post('buckets', [BucketController::class, 'store'])
    ->middleware(['auth:sanctum', 'abilities:buckets:write']);

Route::post('buckets/{bucket}/objects', [ObjectController::class, 'store'])
    ->middleware(['auth:sanctum', 'abilities:objects:write'])
    ->middleware('throttle:uploads');

Route::get('objects/{object}', [ObjectController::class, 'show'])
    ->middleware(['auth:sanctum', 'abilities:objects:read']);

Route::delete('objects/{object}', [ObjectController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'abilities:objects:delete']);

Route::patch('objects/{object}/visibility', [ObjectController::class, 'visibility'])
    ->middleware(['auth:sanctum', 'abilities:objects:write']);

Route::post('objects/{object}/private-url', [ObjectController::class, 'privateUrl'])
    ->middleware(['auth:sanctum', 'abilities:objects:read', 'throttle:private-url']);
