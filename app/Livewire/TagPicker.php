<?php

namespace App\Livewire;

use App\Models\Tag;
use Illuminate\Support\Collection;
use Livewire\Component;

class TagPicker extends Component
{
    public array $tags = [];

    public string $tagInput = '';

    public function allTags(): Collection
    {
        return Tag::all()
            ->filter(fn (Tag $tag) => str_contains(
                strtolower($tag->name),
                strtolower(trim($this->tagInput)),
            ))
            ->sortByDesc(fn (Tag $tag) => in_array($tag->id, $this->tags));
    }

    public function newTag(): void
    {
        if (trim($this->tagInput) === '') {
            return;
        }

        $this->tags[] = Tag::get($this->tagInput)->id;
    }
}
