<?php

namespace App\Models;

use App\Client;
use App\Parser;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Sabre\VObject\Reader;

/**
 * @property int $id
 * @property int $calendar_id
 * @property string $href
 * @property string $etag
 * @property string $ical
 * @property bool $completed
 * @property string $summary
 * @property string $uid
 * @property string $description
 * @property string $due
 * @property int $priority
 * @property array $tags
 * @property ?string $parent_uid
 * @property-read Calendar $calendar
 * @property-read Task $parent
 * @property-read Collection<int, Task> $children
 * @property-read Carbon|null $due_carbon
 * @property-read string $color
 * @property-read string $full_href
 */
class Task extends Model
{
    use HasFactory;

    protected $fillable = ['calendar_id', 'href', 'etag', 'ical'];

    /**
     * @throws Exception
     */
    public function complete(bool $complete = true): void
    {
        $this->completed = $complete;
        $document = Reader::read($this->ical);
        $document->VTODO->STATUS = $this->completed ? 'COMPLETED' : 'NEEDS-ACTION';
        $this->ical = $document->serialize();
        $this->save();
        Client::new($this->calendar->remote)->updateTask($this);
    }

    public function saveOrUpdate(): void
    {
        if (Task::query()->where('calendar_id', $this->calendar_id)->where('href', $this->href)->exists()) {
            $task = Task::query()->where('calendar_id', $this->calendar_id)->where('href', $this->href)->first();

            $task->fill([
                'calendar_id' => $this->calendar_id,
                'href' => $this->href,
                'etag' => $this->etag,
                'ical' => $this->ical,
            ]);

            $task->cache()->save();

            return;
        }

        $this->cache()->save();
    }

    public function cache(): self
    {
        $parser = $this->parser();

        $this->completed = $parser->completed();
        $this->summary = $parser->summary();
        $this->uid = $parser->uid();
        $this->description = $parser->description();
        $this->due = $parser->due();
        $this->priority = $parser->priority();
        $this->tags = $parser->tags();
        $this->parent_uid = $parser->parentUid();

        return $this;
    }

    protected function due(): Attribute
    {
        return Attribute::get(function (string $due) {
            $carbon = Carbon::make($due);

            if ($carbon === null) {
                return '';
            }

            if ($carbon->isToday()) {
                return str_contains($due, 'T') ? $carbon->format('H:i') : 'Today';
            }

            if ($carbon->isTomorrow()) {
                return 'Tomorrow';
            }

            if ($carbon->isAfter(today()->endOfDay()) && $carbon->isBefore(now()->addDays(6))) {
                return match ($carbon->weekday()) {
                    0 => 'Sunday',
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                    default => $carbon->format('d.m.Y'),
                };
            }

            return $carbon->format('d.m.Y');
        });
    }

    protected function dueCarbon(): ?Carbon
    {
        return Carbon::make($this->attributes['due']);
    }

    protected function tags(): Attribute
    {
        return Attribute::get(function (string $tags) {
            return array_filter(
                explode(',', $tags),
                fn(string $tag) => $tags !== '',
            );
        });
    }

    public function color(): Attribute
    {
        return Attribute::get(fn() => match ($this->priority) {
            2, 3 => 'blue',
            4, 5, 6 => 'yellow',
            7, 8, 9 => 'red',
            default => 'gray',
        });
    }

    public function parser(): Parser
    {
        return new Parser($this);
    }

    protected function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_uid', 'uid');
    }

    protected function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_uid', 'uid');
    }

    protected function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    protected function fullHref(): Attribute
    {
        return Attribute::get(fn() => trim($this->calendar->full_href, '/') . '/' . Arr::last(explode('/', (trim($this->href, '/')))));
    }
}
