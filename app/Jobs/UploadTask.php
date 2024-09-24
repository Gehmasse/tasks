<?php

namespace App\Jobs;

use App\Exceptions\ConnectionException;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UploadTask implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Task $task) {}

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $this->task->upload();
    }
}
