<?php

use App\Http\Controllers\ConfirmAttendanceController;
use App\Http\Controllers\GroupController;
use App\Http\Middleware\IncreaseCounter;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('s/{group:uuid}', [GroupController::class, 'show'])->name('group.show')
    ->middleware(IncreaseCounter::using('group'));
Route::post('s/{group:uuid}/confirm', ConfirmAttendanceController::class)->name('group.confirm');

require __DIR__.'/settings.php';
