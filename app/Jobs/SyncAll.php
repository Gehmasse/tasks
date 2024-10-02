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
        logger()->log('info', 'sync started');

        foreach (Remote::all() as $remote) {
            logger()->log('info', 'syncing ' . $remote->name);

            SyncRemote::dispatch($remote);
        }
    }
}
