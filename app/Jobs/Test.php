<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class Test implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        logs()->log('debug', 'test at '.date('Y-m-d H:i:s'));
    }
}
