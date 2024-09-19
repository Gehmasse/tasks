<?php

namespace App\Livewire;

use App\Tasks;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    public string $method;

    public array $params;

    public function render(): View
    {
        return view('livewire.task-list', [
            'tasks' => Tasks::make($this->method, ...$this->params),
        ]);
    }
}
