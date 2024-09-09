<?php

namespace App\View\Components;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskList extends Component
{
    public function __construct(public Paginator $tasks) {}

    public function render(): View
    {
        return view('components.task-list');
    }
}
