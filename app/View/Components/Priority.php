<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Priority extends Component
{
    public function __construct(public \App\Priority $priority) {}

    public function render(): View
    {
        return view('components.priority');
    }
}
