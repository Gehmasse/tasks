<?php

use App\Client;
use App\Models\Tag;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(Client::syncFull(...))
    ->name('sync')
    ->everyFiveMinutes();

Artisan::command('scan-tags', fn () => Tag::scan())
    ->name('scan-tags');
