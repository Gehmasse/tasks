<?php

namespace App\View\Components;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskInline extends Component
{
    public function __construct(public Task $task, public int $indent = 0) {}

    public function prop(string $key): mixed
    {
        return $this->task->parser()->{$key}();
    }

    public function render(): View
    {
        return view('components.task-inline');
    }
}
