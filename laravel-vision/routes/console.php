<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Vision scheduler — synchronizacja albumów co 5 minut, retencja raz dziennie o 3:00
Schedule::command('vision:albums:sync')->everyFiveMinutes()->withoutOverlapping()->runInBackground();
Schedule::command('vision:albums:retention')->dailyAt('03:00');
