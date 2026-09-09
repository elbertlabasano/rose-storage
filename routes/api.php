<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BucketController;
use App\Http\Controllers\ObjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('objects/{object}/public', [ObjectController::class, 'publicDownload'])
    ->name('objects.public-download');

Route::get('objects/{object}/private', [ObjectController::class, 'privateDownload'])
    ->middleware('signed')
    ->name('objects.private-download');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::apiResource('buckets', BucketController::class)->only(['index', 'store', 'show']);
    Route::post('buckets/{bucket}/objects', [ObjectController::class, 'store'])
        ->middleware('throttle:uploads');
    Route::get('objects/{object}', [ObjectController::class, 'show']);
    Route::delete('objects/{object}', [ObjectController::class, 'destroy']);
    Route::patch('objects/{object}/visibility', [ObjectController::class, 'visibility']);
    Route::post('objects/{object}/private-url', [ObjectController::class, 'privateUrl'])
        ->middleware('throttle:private-url');
});


