<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Task extends Component
{
    public function __construct(public \App\Models\Task $task, public int $indent = 0) {}

    public function prop(string $key): mixed
    {
        return $this->task->parser()->{$key}();
    }

    public function render(): View
    {
        return view('components.task');
    }
}
