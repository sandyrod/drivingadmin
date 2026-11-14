<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Lesson reminders (email + SMS). Needs the Laravel scheduler running:
// `php artisan schedule:work` locally, or a cron entry `* * * * * php artisan schedule:run` on the server.
Schedule::command('lessons:send-reminders')->everyMinute()->withoutOverlapping();
