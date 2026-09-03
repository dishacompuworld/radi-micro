<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('logs:clear', function() {
    exec('echo "" > ' . storage_path('logs/laravel.log'));
    $this->info('Logs have been cleared');
})->describe('Clear log files');
