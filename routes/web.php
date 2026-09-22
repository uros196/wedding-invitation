<?php

use App\Http\Controllers\ConfirmAttendanceController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MemoryWallController;
use App\Http\Controllers\MemoryWallDownloadController;
use App\Http\Controllers\MemoryWallShareController;
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

    Route::controller(MemoryWallController::class)
        ->prefix('/memory-wall/{wedding:uuid}')
        ->name('memory-wall.')
        ->group(function () {

            // Wedding's memory wall page
            Route::get('', 'show')->name('show');

            // Wedding's memory wall upload API
            Route::post('/upload/initialize', 'initializeUpload')->name('upload.initialize')
                ->middleware('throttle:120,1');

            // Upload sessions are resolved by UUID and then authorized against the wedding
            // and private token in the upload actions; they are not nested bindings.
            Route::post('/upload/{upload:uuid}/parts', 'getUploadPartUrls')->name('upload.parts')
                ->withoutScopedBindings()
                ->middleware('throttle:120,1');
            Route::post('/upload/{upload:uuid}/complete', 'completeUpload')->name('upload.complete')
                ->withoutScopedBindings()
                ->middleware('throttle:120,1');
            Route::delete('/upload/{upload:uuid}', 'cancelUpload')->name('upload.cancel')
                ->withoutScopedBindings()
                ->middleware('throttle:120,1');
        });
});

Route::middleware('auth:wedding')
    ->prefix('wedding/memory-wall/{wedding:uuid}')
    ->scopeBindings()
    ->name('memory-wall.')
    ->group(function (): void {
        // Authorize the upload before redirecting to its original storage object.
        Route::get('/download/{memoryWallUpload}', [MemoryWallDownloadController::class, 'single'])
            ->name('download.single');

        // Archive requests resolve by UUID and may redirect only after preparation.
        Route::get('/downloads/{memoryWallDownload}/file', [MemoryWallDownloadController::class, 'archive'])
            ->name('downloads.file');
    });

Route::controller(MemoryWallShareController::class)
    ->prefix('memory-wall/share/{share:uuid}')
    ->name('memory-wall.share.')
    ->group(function (): void {
        Route::get('', 'show')->name('show');
        Route::post('/unlock', 'unlock')->name('unlock')
            ->middleware('throttle:5,1');
    });
