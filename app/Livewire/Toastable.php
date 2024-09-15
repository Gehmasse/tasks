<?php

namespace App\Livewire;

trait Toastable
{
    protected function toast(string $message, string $color): void
    {
        $this->dispatch('toast', ['message' => $message, 'color' => $color]);
    }
}
