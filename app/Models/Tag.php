<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $color
 * @property string $icon
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'icon'];

    private static array $colors = [
        'antiquewhite',
        '#aeffe4',
        '#c9e6ff',
        'aquamarine',
        'bisque',
        '#dfbcff',
        '#afffaf',
    ];

    public static function get(string $name, string $color = '', string $icon = ''): self
    {
        $tag = self::query()->where('name', $name)->first();

        if ($tag !== null) {
            return $tag;
        }

        return static::create([
            'name' => $name,
            'color' => $color === '' ? self::randomColor() : $color,
            'icon' => $icon,
        ]);
    }

    private static function randomColor(): string
    {
        return self::$colors[array_rand(self::$colors)];
    }

    public static function allTags(): Collection
    {
        return self::query()->get()->filter(fn (self $tag) => ! str_starts_with($tag->name, '@'));
    }

    public static function allPeople(): Collection
    {
        return self::query()->get()->filter(fn (self $tag) => str_starts_with($tag->name, '@'));
    }

    public static function scan(): void
    {
        foreach (Task::lazy() as $task) {
            foreach ($task->tags as $tag) {
                self::get($tag);
            }
        }
    }

    public function nameWithoutPrefix(): string
    {
        return str_starts_with($this->name, '@') ? substr($this->name, 1) : $this->name;
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $name) => trim($name),
            set: fn (string $name) => trim($name),
        );
    }

    protected function icon(): Attribute
    {
        return Attribute::make(
            get: function (string $icon) {
                if (str_starts_with($this->name, '@')) {
                    return 'bi-person-fill';
                }

                if (trim($icon) === '') {
                    return 'bi-tag-fill';
                }

                return trim($icon);
            },
            set: fn (string $icon) => trim($icon),
        );
    }
}
