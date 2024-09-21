<?php

namespace App\Livewire;

use App\Tasks;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    public string $filter;

    public array $params;

    public function render(): View
    {
        return view('livewire.task-list', [
            'tasks' => Tasks::make($this->filter, ...$this->params),
        ]);
    }
}
