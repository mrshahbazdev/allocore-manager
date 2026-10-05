<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The self-improving loop runs unattended: re-evaluate every company's
// open challenges each night so recommendations stay fresh.
Schedule::command('allocore:refresh')->daily();
