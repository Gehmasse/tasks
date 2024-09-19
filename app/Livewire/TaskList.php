<?php

namespace App\Livewire;

use App\Tasks;
use Illuminate\View\View;
use Livewire\Component;

class TaskList extends Component
{
    public string $method;
    public array $params;

    public function render(): View
    {
        return view('livewire.task-list', [
            'tasks' => Tasks::make($this->method, ...$this->params),
        ]);
    }
}
