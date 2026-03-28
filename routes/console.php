<?php

use App\Console\Commands\SendMoodReminder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Daily mood reminder push notification — fires every day at 09:00 server time
Schedule::command(SendMoodReminder::class)->dailyAt('09:00');
