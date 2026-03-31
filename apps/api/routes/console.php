<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment('SignalCore local runtime is alive.');
})->purpose('Display a lightweight runtime confirmation message');

Schedule::command('platform:scheduler-tick')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
