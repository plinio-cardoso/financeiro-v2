<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule notification commands
Schedule::command('notify:due-today')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('notify:overdue')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onOneServer();

// Automatically generate recurring transactions every 5 minutes
Schedule::command('app:generate-transactions')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();
