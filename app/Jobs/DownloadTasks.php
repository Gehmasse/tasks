<?php

namespace App\Jobs;

use App\Client;
use App\Models\Calendar;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;

class DownloadTasks implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Calendar $calendar,
        private readonly array $hrefs,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function handle(): void
    {
        foreach (Client::tasks($this->calendar, hrefs: $this->hrefs) as $task) {
            $task->createOrUpdate();
        }
    }
}
