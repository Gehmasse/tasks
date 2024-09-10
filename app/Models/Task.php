<?php

namespace App\Models;

use App\Client;
use App\Exceptions\ConnectionException;
use App\Exceptions\UidMismatchException;
use App\Parser\Parser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Override;

/**
 * @property int $id
 * @property int $calendar_id
 * @property string $href
 * @property string $etag
 * @property string $ical
 * @property bool $needs_upload
 *
 * cached properties:
 * @property bool $completed
 * @property string $summary
 * @property-read string $uid
 * @property string $description
 * @property string $due
 * @property int $priority
 * @property array $tags
 * @property ?string $parent_uid
 *
 * relations & calculated:
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

    protected $attributes = ['needs_upload' => false];

    public function upload(): void
    {
        try {
            Client::new($this->calendar->remote)->updateTask($this);
            $this->needs_upload = false;
        } catch (ConnectionException) {
            $this->needs_upload = true;
        }
    }

    #[Override]
    public function save(array $options = []): bool
    {
        $this->cache();

        if ($this->completed === null) {
            dd($this->toArray());
        }

        return parent::save($options);
    }

    public function saveOrUpdate(): void
    {
        $task = Task::query()
            ->where('calendar_id', $this->calendar_id)
            ->where('href', $this->href)
            ->first();

        if ($task === null) {
            $this->save();

            return;
        }

        if ($this->uid !== $task->uid) {
            throw new UidMismatchException('tasks '.$this->id.' and '.$task->id.' have same href, but different uids');
        }

        $task->fill([
            'calendar_id' => $this->calendar_id,
            'etag' => $this->etag,
            'ical' => $this->ical,
        ])->save();
    }

    private function cache(): self
    {
        // value stored here, because is set to true when
        // changing the attributes in the following
        $needs_upload = $this->needs_upload;

        $parser = $this->parser();

        $this->attributes['completed'] = $parser->completed();
        $this->attributes['summary'] = $parser->summary();
        $this->attributes['uid'] = $parser->uid();
        $this->attributes['description'] = $parser->description();
        $this->attributes['due'] = $parser->due();
        $this->attributes['priority'] = $parser->priority();
        $this->attributes['tags'] = $parser->tags();
        $this->attributes['parent_uid'] = $parser->parentUid() ?? '';
        $this->attributes['needs_upload'] = $needs_upload;

        return $this;
    }

    protected function completed(): Attribute
    {
        return Attribute::set(fn (bool $value) => $this->parser()->setCompleted($value));
    }

    protected function summary(): Attribute
    {
        return Attribute::set(fn (string $value) => $this->parser()->setSummary($value));
    }

    protected function description(): Attribute
    {
        return Attribute::set(fn (string $value) => $this->parser()->setDescription($value));
    }

    protected function due(): Attribute
    {
        return Attribute::make(
            get: function () {
                $raw = $this->parser()->due();

                $carbon = Carbon::make($raw);

                if ($carbon === null) {
                    return '';
                }

                if ($carbon->isToday()) {
                    return str_contains($raw, 'T') ? $carbon->format('H:i') : 'Today';
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
            },
            set: fn (string $value) => $this->parser()->setDue($value),
        );
    }

    protected function priority(): Attribute
    {
        return Attribute::set(fn (int $value) => $this->parser()->setPriority($value));
    }

    protected function tags(): Attribute
    {
        return Attribute::make(
            get: function () {
                $tags = $this->parser()->tags();

                return array_filter(
                    explode(',', $tags),
                    fn (string $tag) => $tags !== '',
                );
            },
            set: fn (array|string $value) => $this->parser()->setTags(is_array($value) ? $value : [$value]),
        );
    }

    protected function parentUid(): Attribute
    {
        return Attribute::set(fn (?string $value) => $this->parser()->setParentUid($value));
    }

    protected function dueCarbon(): Attribute
    {
        return Attribute::get(fn () => Carbon::make($this->attributes['due']));
    }

    public function color(): Attribute
    {
        return Attribute::get(fn () => match ($this->priority) {
            2, 3 => 'blue',
            4, 5, 6 => 'yellow',
            7, 8, 9 => 'red',
            default => 'gray',
        });
    }

    protected function fullHref(): Attribute
    {
        return Attribute::get(
            fn () => trim($this->calendar->full_href, '/')
                .'/'.Arr::last(explode('/', (trim($this->href, '/'))))
        );
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
}
