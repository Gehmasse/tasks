<?php

namespace App\Parser;

trait Getters
{
    public function completed(): bool
    {
        return (string) $this->vtodo->STATUS === 'COMPLETED';
    }

    public function summary(): string
    {
        return (string) $this->vtodo->SUMMARY;
    }

    public function uid(): string
    {
        return (string) $this->vtodo->UID;
    }

    public function description(): string
    {
        return (string) $this->vtodo->DESCRIPTION;
    }

    public function due(): string
    {
        return (string) $this->vtodo->DUE;
    }

    public function priority(): int
    {
        return (int) (string) ($this->vtodo->PRIORITY ?? 0);
    }

    public function tags(): string
    {
        return $this->vtodo->CATEGORIES ?? '';
    }

    public function parentUid(): ?string
    {
        foreach ($this->vtodo->select('RELATED-TO') as $relatedTo) {
            if (isset($relatedTo['RELTYPE']) && (string) $relatedTo['RELTYPE'] === 'PARENT') {
                return (string) $relatedTo->getValue();
            }

            return (string) $relatedTo;
        }

        return null;
    }
}
