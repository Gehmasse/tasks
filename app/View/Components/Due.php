<?php

namespace App\View\Components;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Due extends Component
{
    public function __construct(public Task $task) {}

    public function render(): View
    {
        return view('components.due');
    }
}
