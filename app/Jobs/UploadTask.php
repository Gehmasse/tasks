<?php

namespace App\Jobs;

use App\Client;
use App\Exceptions\CalDavException;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;

class UploadTask implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Task $task) {}

    /**
     * @throws CalDavException
     * @throws ConnectionException
     */
    public function handle(): void
    {
        Client::updateTask($this->task);
    }
}
