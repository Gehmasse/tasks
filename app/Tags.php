<?php

namespace App;

use App\Models\Task;
use Illuminate\Support\Collection;

class Tags
{
    private static array $colors = [
        'antiquewhite',
        '#aeffe4',
        '#c9e6ff',
        'aquamarine',
        'bisque',
        '#dfbcff',
        '#afffaf',
    ];

    private static array $tags = [];

    public static function color(string $tag): string
    {
        if (! array_key_exists($tag, self::$tags)) {
            shuffle(self::$colors);
            self::$tags[$tag] = self::$colors[0];
        }

        return self::$tags[$tag];
    }

    public static function all(): Collection
    {
        return Task::all()
            ->flatMap(fn (Task $task) => $task->tags)
            ->unique()
            ->sort();
    }
}
