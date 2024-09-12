<?php

namespace App\Models;

use App\Client;
use App\Exceptions\ConnectionException;
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
 * @property-read string $due_formatted
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
        $this->writeToIcs();
        $return = parent::save($options);
        $this->cache();
        $this->needs_upload = true;

        return $return;
    }

    public function storeOrUpdate(): void
    {
        $task = Task::query()
            ->where('calendar_id', $this->calendar_id)
            ->where('href', $this->href)
            ->first();

        if ($task === null) {
            $this->save();

            return;
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

        $this->completed = $parser->completed();
        $this->summary = $parser->summary();
        $this->uid = $parser->uid();
        $this->description = $parser->description();
        $this->due = $parser->due();
        $this->priority = $parser->priority();
        $this->tags = explode(',', $parser->tags());
        $this->parent_uid = $parser->parentUid() ?? '';
        $this->needs_upload = $needs_upload;

        return $this;
    }

    private function writeToIcs(): self
    {
        $parser = $this->parser();

        $parser->setCompleted($this->completed);
        $parser->setSummary($this->summary);
        $parser->setDescription($this->description);
        $parser->setDue($this->due);
        $parser->setPriority($this->priority);
        $parser->setTags($this->tags);
        $parser->setParentUid($this->parent_uid);

        $this->ical = $parser->serialise();

        return $this;
    }

    public function hasDue(): bool
    {
        return trim($this->due) !== '';
    }

    public function hasDueTime(): bool
    {
        return $this->hasDue() && str_contains($this->due, 'T');
    }

    public function priority(): object
    {
        return new class($this) {
            public function __construct(private readonly Task $task)
            {
            }

            public function none(): bool
            {
                return $this->task->priority <= 0;
            }

            public function low(): bool
            {
                return $this->task->priority >= 1 && $this->task->priority <= 3;
            }

            public function mid(): bool
            {
                return $this->task->priority >= 5 && $this->task->priority <= 6;
            }

            public function high(): bool
            {
                return $this->task->priority > 7;
            }

            public function color(): string
            {
                return match (true) {
                    $this->low() => 'blue',
                    $this->mid() => 'yellow',
                    $this->high() => 'red',
                    default => 'gray',
                };
            }
        };
    }

    protected function dueFormatted(): Attribute
    {
        return Attribute::get(function () {
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
        });
    }

    protected function tags(): Attribute
    {
        return Attribute::make(
            get: function (string $tags) {
                return array_filter(
                    explode(',', $tags),
                    fn(string $tag) => $tags !== '',
                );
            },
            set: function (array $tags) {
                return join(',', $tags);
            },
        );
    }

    protected function dueCarbon(): Attribute
    {
        return Attribute::get(fn() => Carbon::make($this->attributes['due']));
    }

    protected function fullHref(): Attribute
    {
        return Attribute::get(
            fn() => trim($this->calendar->full_href, '/')
                . '/' . Arr::last(explode('/', (trim($this->href, '/'))))
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
