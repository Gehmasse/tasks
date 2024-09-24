<?php

namespace App\Jobs;

use App\Exceptions\StatusCodeException;
use App\Models\Remote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;

class SyncRemote implements ShouldQueue
{
    use Queueable;

    public function __construct(readonly private Remote $remote) {}

    /**
     * @throws ConnectionException
     * @throws StatusCodeException
     */
    public function handle(): void
    {
        $this->remote->sync();
    }
}
