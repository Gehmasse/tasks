<?php

namespace App\Jobs;

use App\Client;
use App\Exceptions\ConnectionException;
use App\Models\Calendar;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
    public function handle(): void {
        $client = Client::new($this->calendar->remote);

        foreach ($client->tasks($this->calendar, hrefs: $this->hrefs) as $task) {
            $task->createOrUpdate();
        }
    }
}
