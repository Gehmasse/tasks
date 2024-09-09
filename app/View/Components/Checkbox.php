<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Checkbox extends Component
{
    public function __construct(public \App\Models\Task $task) {}

    public function render(): View
    {
        return view('components.checkbox');
    }
}
