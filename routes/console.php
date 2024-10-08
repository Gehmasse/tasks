<?php

use App\Jobs\SyncAll;
use App\Models\Tag;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('scan-tags', fn () => Tag::scan())
    ->name('scan-tags');

Schedule::call(function () {
    SyncAll::dispatch();
})->everyMinute();
