<?php

use App\Http\Controllers\ConfirmAttendanceController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MemoryWallController;
use App\Http\Middleware\IncreaseCounter;
use App\Http\Middleware\XSSProtection;
use Illuminate\Support\Facades\Route;

Route::prefix('wedding')->group(function () {
    // Personalized invitation page
    Route::get('/{group:uuid}', [GroupController::class, 'show'])->name('group.show')
        ->middleware(IncreaseCounter::using('group'));

    // RSVP submit API
    Route::post('/{group:uuid}/confirm', ConfirmAttendanceController::class)->name('group.confirm')
        ->middleware(XSSProtection::class)
        ->middleware('throttle:10,1');

    // Wedding's memory wall page
    Route::get('/memory-wall/{wedding:uuid}', [MemoryWallController::class, 'show'])->name('memory-wall.show');

    // Wedding's memory wall upload API
    Route::post('/memory-wall/{wedding:uuid}/upload', [MemoryWallController::class, 'upload'])->name('memory-wall.upload')
        ->middleware('throttle:10,1');
});
