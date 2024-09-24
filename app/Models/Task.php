<?php

namespace App\Models;

use App\Client;
use App\Jobs\UploadTask;
use App\Parser\Parser;
use App\Priority;
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
 *
 * cached properties:
 * @property bool $completed
 * @property string $summary
 * @property string $uid
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

    public function upload(): void
    {
        Client::updateTask($this);
    }

    #[Override]
    public function save(array $options = [], bool $preventUpload = false): bool
    {
        $this->writeToIcs();
        $return = parent::save($options);
        $this->cache();

        if (! $preventUpload) {
            UploadTask::dispatch($this);
        }

        return $return;
    }

    public function createOrUpdate(): self
    {
        $task = Task::query()
            ->where('calendar_id', $this->calendar_id)
            ->where('href', $this->href)
            ->first();

        if ($task === null) {
            $this->cache()->save(preventUpload: true);

            return $this;
        }

        $task->fill([
            'calendar_id' => $this->calendar_id,
            'etag' => $this->etag,
            'ical' => $this->ical,
        ])->cache()->save(preventUpload: true);

        return $task;
    }

    private function cache(): self
    {
        $parser = $this->parser();

        $this->completed = $parser->completed();
        $this->summary = $parser->summary();
        $this->uid = $parser->uid();
        $this->description = $parser->description();
        $this->due = $parser->due();
        $this->priority = $parser->priority();
        $this->tags = $parser->tags();
        $this->parent_uid = $parser->parentUid() ?? '';

        return $this;
    }

    public function writeToIcs(): self
    {
        $parser = $this->parser();

        $parser->setCompleted($this->completed);
        $parser->setSummary($this->summary);
        $parser->setUid($this->uid);
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

    public function priority(): Priority
    {
        return new Priority($this->priority);
    }

    public function createAndUploadInitially(): void
    {
        $this->writeToIcs();
        $this->save();
        $this->upload();
    }

    public function tagObjects(): Collection
    {
        return collect($this->tags)
            ->map(fn (string $tag) => Tag::get($tag));
    }

    protected function dueFormatted(): Attribute
    {
        return Attribute::get(function () {
            $raw = $this->parser()->due();

            $carbon = Carbon::make($raw);

            if ($carbon === null) {
                return '';
            }

            if ($carbon->isYesterday()) {
                return 'Yesterday';
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
                if (! json_validate($tags)) {
                    return [];
                }

                $tags = array_filter(json_decode($tags), fn (string $tag) => trim($tag) !== '');

                foreach ($tags as $tag) {
                    Tag::get($tag);
                }

                return $tags;
            },
            set: fn (array $tags) => json_encode(array_filter($tags, fn (string $tag) => trim($tag) !== '')),
        );
    }

    protected function dueCarbon(): Attribute
    {
        return Attribute::get(fn () => Carbon::make($this->attributes['due']));
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
