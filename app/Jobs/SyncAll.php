<?php

namespace App\Jobs;

use App\Models\Remote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAll implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        foreach (Remote::all() as $remote) {
            SyncRemote::dispatch($remote);
        }
    }
}
