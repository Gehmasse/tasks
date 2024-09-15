<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Component;

class TaskInline extends Component
{
    public Task $task;

    public int $indent = 0;

    public bool $completed;

    public bool $showChildren;

    public function mount(): void
    {
        $this->completed = $this->task->completed;
        $this->showChildren = session('show-children-for-'.$this->task->id, true);
    }

    public function updated(string $prop): void
    {
        if ($prop === 'completed') {
            $this->task->completed = $this->completed;
            $this->task->save();
            $this->task->upload();

            $this->dispatch('toast', [
                'color' => 'green',
                'message' => $this->task->completed
                    ? 'Task '.$this->task->id.' is completed'
                    : 'Task '.$this->task->id.' is not completed anymore',
            ]);
        }
    }

    public function toggleChildren(): void
    {
        $this->showChildren = ! $this->showChildren;
        session(['show-children-for-'.$this->task->id => $this->showChildren]);
    }
}
