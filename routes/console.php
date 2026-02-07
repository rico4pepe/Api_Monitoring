<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ✅ Incident detection schedule (ADD THIS)
Schedule::command('incidents:detect-sms')
    ->everyMinute()
    ->withoutOverlapping();