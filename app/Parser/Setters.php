<?php

namespace App\Parser;

trait Setters
{
    public function setCompleted(bool $value): void
    {
        $this->vtodo->STATUS = $value ? 'COMPLETED' : 'NEEDS-ACTION';
        $this->persist();
    }

    public function setSummary(string $value): void
    {
        $this->vtodo->SUMMARY = $value;
        $this->persist();
    }

    public function setDescription(string $value): void
    {
        $this->vtodo->DESCRIPTION = $value;
        $this->persist();
    }

    public function setDue(string $value): void
    {
        $this->vtodo->DUE = $value;
        $this->persist();
    }

    public function setPriority(int $value): void
    {
        $this->vtodo->PRIORITY = $value;
        $this->persist();
    }

    public function setTags(array $value): void
    {
        $this->vtodo->CATEGORIES = implode(',', $value);
        $this->persist();
    }

    public function setParentUid(?string $value): void
    {
        $this->vtodo->{'RELATED-TO'} = $value;
        $this->persist();
    }
}
