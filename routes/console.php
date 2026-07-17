<?php

use App\Jobs\RefreshOverdueStatuses;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Laravel 11 : plus de app/Console/Kernel.php, la planification vit ici.
Schedule::job(new RefreshOverdueStatuses)
    ->dailyAt('00:01')
    ->name('refresh-overdue-statuses')
    ->withoutOverlapping();
