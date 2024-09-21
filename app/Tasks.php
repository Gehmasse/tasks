<?php

namespace App;

use App\Models\Calendar;
use App\Models\Filter;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Tasks
{
    public static function make(string $filter, mixed ...$params): Paginator
    {
        return match ($filter) {
            'all' => self::all(),
            'today' => self::today(),
            'tomorrow' => self::tomorrow(),
            'forCalendar' => self::forCalendar(...$params),
            'forTag' => self::forTag(...$params),
            'search' => self::search(...$params),
            'lastModified' => self::lastModified(),
            default => self::parse(Filter::find($filter)->filter),
        };
    }

    private static function base(bool $hideChildren = true): Builder
    {
        $builder = Task::query();

        if (! session('completed', false)) {
            $builder = $builder->where('completed', false);
        }

        if ($hideChildren) {
            $builder = $builder->where('parent_uid', '');
        }

        return $builder
            ->orderByRaw('case when due is null then 1 else 0 end')
            ->orderBy('due')
            ->orderBy('priority', 'desc');
    }

    private static function all(): Paginator
    {
        return self::base()
            ->paginate(self::perPage());
    }

    private static function today(): Paginator
    {
        return self::base()
            ->whereNot('due', '')
            ->whereLike('due', '%'.now()->format('Ymd').'%')
            ->orWhere(fn (Builder $builder) => $builder
                ->whereNot('due', '')
                ->where('due', '<', now()->format('Ymd'))
                ->where('completed', false)
                ->where('parent_uid', ''))
            ->paginate(self::perPage());
    }

    private static function tomorrow(): Paginator
    {
        return self::base()
            ->whereNot('due', '')
            ->whereLike('due', '%'.Carbon::tomorrow()->format('Ymd').'%')
            ->orWhere(fn (Builder $builder) => $builder
                ->whereNot('due', '')
                ->where('due', '<', Carbon::tomorrow()->format('Ymd'))
                ->where('completed', false)
                ->where('parent_uid', ''))
            ->paginate(self::perPage());
    }

    private static function forCalendar(Calendar $calendar): Paginator
    {
        return self::base()
            ->where('calendar_id', $calendar->id)
            ->paginate(self::perPage());
    }

    private static function forTag(Tag $tag): Paginator
    {
        return self::base()
            ->whereJsonContains('tags', $tag->name)
            ->paginate(self::perPage());
    }

    private static function search(string $search): Paginator
    {
        return self::base(hideChildren: false)
            ->where(fn (Builder $builder) => $builder
                ->orWhereRaw('lower(summary) like ? ', ['%'.$search.'%'])
                ->orWhereRaw('lower(description) like ? ', ['%'.$search.'%'])
                ->orWhereRaw('lower(tags) like ? ', ['%'.$search.'%']))
            ->paginate(self::perPage());
    }

    private static function lastModified(): Paginator
    {
        return Task::query()
            ->orderByDesc('updated_at')
            ->paginate(self::perPage());
    }

    public static function perPage(): int
    {
        return session('per-page', 15);
    }

    /**
     * filter must be a json with [{type, value, bool=and}]
     *
     * @return void
     */
    private static function parse(string $key): Paginator
    {
        if (! json_validate($key)) {
            dd('invalid filter: '.$key);
        }

        $filter = json_decode($key);

        if (! is_array($filter)) {
            dd('invalid filter: '.$key);
        }

        $builder = Task::query();

        foreach ($filter as $step) {
            $builder = self::parseStep($builder, $step);
        }

        return $builder->paginate(self::perPage());
    }

    private static function parseStep(Builder $builder, object $step): Builder
    {
        $type = $step->type;
        $value = $step->value;
        $bool = $step->bool ?? 'and';

        if (! isset($type, $value, $bool) || ! in_array($bool, ['and', 'or'])) {
            dd('invalid filter line: '.json_encode($step));
        }

        if ($type === 'completed') {
            return $builder->where('completed', $value, boolean: $bool);
        }

        if ($type === 'tag') {
            return $builder->whereJsonContains('tags', $value, boolean: $bool);
        }

        if ($type === 'person') {
            return $builder->whereJsonContains('tags', '@'.$value, boolean: $bool);
        }

        if ($type === 'calendar') {
            return $builder->where('calendar_id', $value, boolean: $bool);
        }

        dd('invalid filter line: '.json_encode($step));
    }
}
