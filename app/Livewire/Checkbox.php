<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Checkbox extends Component
{
    #[Modelable]
    public bool $checked;

    public function id(): string
    {
        return once(fn () => (string) Str::uuid());
    }
}
