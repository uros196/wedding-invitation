<?php

use App\Jobs\CleanupExpiredMemoryWallDownloadsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Release expired and cancelled archive objects once per day without overlapping workers.
Schedule::job(new CleanupExpiredMemoryWallDownloadsJob)
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();
