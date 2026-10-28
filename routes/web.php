<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/login', 'dashboard')->name('login');
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/backups', 'dashboard')->name('backups');
Route::get('/backup-downloads/{id}', [\App\Http\Controllers\BackupController::class, 'download'])
    ->whereUuid('id')->middleware(['signed', 'throttle:20,1'])->name('backups.download');
