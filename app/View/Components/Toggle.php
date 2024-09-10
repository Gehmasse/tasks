<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toggle extends Component
{
    public function __construct(
        public bool $checked,
        public string $color,
        public string $on,
        public string $off,
        public string $label,
    ) {}

    public function render(): View
    {
        return view('components.toggle');
    }
}
