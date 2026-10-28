<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\KeycloakController;

Route::middleware('throttle:auth')->group(function () {
    Route::get('/auth/keycloak/redirect', [KeycloakController::class, 'redirect'])->name('sso.redirect');
    Route::get('/auth/keycloak/callback', [KeycloakController::class, 'callback'])->name('sso.callback');
});
Route::post('/auth/keycloak/session', [KeycloakController::class, 'session'])->middleware(['auth:web', 'throttle:auth'])->name('sso.session');
Route::post('/auth/keycloak/logout', [KeycloakController::class, 'logout'])->middleware('auth:web')->name('sso.logout');

Route::view('/', 'home')->name('home');
Route::view('/login', 'dashboard')->name('login');
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/backups', 'dashboard')->name('backups');
Route::get('/backup-downloads/{id}', [\App\Http\Controllers\BackupController::class, 'download'])
    ->whereUuid('id')->middleware(['signed', 'throttle:20,1'])->name('backups.download');
