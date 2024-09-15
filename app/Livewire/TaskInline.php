<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Component;

class TaskInline extends Component
{
    public Task $task;

    public int $indent = 0;

    public bool $completed;

    public function mount(): void
    {
        $this->completed = $this->task->completed;
    }

    public function updated(string $prop): void
    {
        if ($prop === 'completed') {
            $this->task->completed = $this->completed;
            $this->task->save();
            $this->task->upload();
        }
    }
}
