<?php

namespace App\Parser;

use App\Models\Task;
use Sabre\VObject\Component\VTodo;
use Sabre\VObject\Document;
use Sabre\VObject\Reader;

readonly class Parser
{
    use Getters;
    use Setters;

    private Document $document;

    private VTodo $vtodo;

    public function __construct(private Task $task)
    {
        $this->document = Reader::read($this->task->ical);
        $this->vtodo = $this->document->VTODO;
    }

    public function serialise(): string
    {
        return $this->document->serialize();
    }
}
