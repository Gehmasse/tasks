<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tag extends Component
{
    public function __construct(public  string $tag) {}

    public function render(): View
    {
        return view('components.tag');
    }
}
