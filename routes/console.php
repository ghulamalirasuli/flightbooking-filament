<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// This tells the scheduler to run your command every minute
Schedule::command('comments:send-reminders')->everyMinute();

// Optional: Add logging to see if it's running
Schedule::command('comments:send-reminders')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/reminders.log'));

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');