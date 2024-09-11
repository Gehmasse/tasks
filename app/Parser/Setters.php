<?php

namespace App\Parser;

trait Setters
{
    public function setCompleted(bool $value): void
    {
        $this->vtodo->STATUS = $value ? 'COMPLETED' : 'NEEDS-ACTION';
    }

    public function setSummary(string $value): void
    {
        $this->vtodo->SUMMARY = $value;
    }

    public function setDescription(string $value): void
    {
        $this->vtodo->DESCRIPTION = $value;
    }

    public function setDue(string $value): void
    {
        $this->vtodo->remove('DUE');

        if ($value !== '') {
            $this->vtodo->add('DUE', $value, ['VALUE' => 'DATE']);
        }
    }

    public function setPriority(int $value): void
    {
        $this->vtodo->PRIORITY = $value;
    }

    public function setTags(array $value): void
    {
        $this->vtodo->CATEGORIES = implode(',', $value);
    }

    public function setParentUid(?string $value): void
    {
        // TODO: test this

        if ($value === null || $value === '') {
            $this->vtodo->remove('RELATED-TO');
        } else {
            $this->vtodo->{'RELATED-TO'} = $value;
        }
    }
}
