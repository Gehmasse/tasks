<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UploadTask implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Task $task) {}

    public function handle(): void
    {
        $this->task->upload();
    }
}
