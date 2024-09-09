<?php

namespace App;

use App\Models\Task;
use Sabre\VObject\Component\VTodo;
use Sabre\VObject\Reader;

readonly class Parser
{
    public function __construct(private Task $task)
    {
    }

    private function parsed(): VTodo
    {
        return once(fn() => Reader::read($this->task->ical)->VTODO);
    }

    public function completed(): bool
    {
        return (string)$this->parsed()->STATUS === 'COMPLETED';
    }

    public function summary(): string
    {
        return (string)$this->parsed()->SUMMARY;
    }

    public function uid(): string
    {
        return (string)$this->parsed()->UID;
    }

    public function description(): string
    {
        return (string)$this->parsed()->DESCRIPTION;
    }

    public function due(): string
    {
        return (string)$this->parsed()->DUE;
    }

    public function priority(): int
    {
        return (int)(string)($this->parsed()->PRIORITY ?? 0);
    }

    public function tags(): string
    {
        return $this->parsed()->CATEGORIES ?? '';
    }

    public function parentUid(): ?string
    {
        foreach ($this->parsed()->select('RELATED-TO') as $relatedTo) {
            if (isset($relatedTo['RELTYPE']) && (string)$relatedTo['RELTYPE'] === 'PARENT') {
                return (string)$relatedTo->getValue();
            }

            return (string)$relatedTo;
        }

        return null;
    }
}
