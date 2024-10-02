<?php

namespace App\Livewire;

use App\Models\Task;
use Illuminate\Support\Carbon;
use Livewire\Component;

class SyncStatus extends Component
{
    public Carbon $init;

    public function latestUpdate(): Carbon
    {
        return Task::query()->latest()->first(['updated_at'])?->updated_at ?? today();
    }
}
