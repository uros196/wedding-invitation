<?php

use App\Http\Controllers\ConfirmAttendanceController;
use App\Http\Controllers\GroupController;
use App\Http\Middleware\IncreaseCounter;
use Illuminate\Support\Facades\Route;


// Personalized invitation page
Route::get('s/{group:uuid}', [GroupController::class, 'show'])->name('group.show')
    ->middleware(IncreaseCounter::using('group'));

// RSVP submit API
Route::post('s/{group:uuid}/confirm', ConfirmAttendanceController::class)->name('group.confirm');
