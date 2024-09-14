<?php

namespace App;

class Priority
{
    public function __construct(private readonly int $priority) {}

    public function none(): bool
    {
        return $this->priority <= 0;
    }

    public function high(): bool
    {
        return $this->priority >= 1 && $this->priority <= 3;
    }

    public function mid(): bool
    {
        return $this->priority >= 5 && $this->priority <= 6;
    }

    public function low(): bool
    {
        return $this->priority > 7;
    }

    public function color(): string
    {
        return match (true) {
            $this->low() => 'blue',
            $this->mid() => 'yellow',
            $this->high() => 'red',
            default => 'gray',
        };
    }
}
