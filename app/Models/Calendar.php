<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $remote_id
 * @property string $href
 * @property string $name
 * @property string $ctag
 * @property string $color
 * @property-read Remote $remote
 * @property-read Collection<int, Task> $tasks
 * @property-read $full_href
 */
class Calendar extends Model
{
    use HasFactory;

    protected $fillable = ['remote_id', 'href', 'name', 'ctag', 'color'];

    public static function default(): ?self
    {
        return self::find(session('calendar.default'));
    }

    public function saveOrUpdate(): void
    {
        if (Calendar::query()->where('remote_id', $this->remote_id)->where('href', $this->href)->exists()) {
            $calendar = Calendar::query()->where('remote_id', $this->remote_id)->where('href', $this->href)->first();

            $calendar->name = $this->name;
            $calendar->color = $this->color;

            $calendar->save();

            return;
        }

        $this->save();
    }

    protected function remote(): BelongsTo
    {
        return $this->belongsTo(Remote::class);
    }

    protected function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    protected function fullHref(): Attribute
    {
        return Attribute::get(fn () => trim($this->remote->href, '/').'/'.Arr::last(explode('/', (trim($this->href, '/')))));
    }
}
