<?php

namespace App;

use App\Models\Calendar;
use App\Models\Task;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class Tasks
{
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

    public static function all(): Paginator
    {
        return self::base()
            ->paginate(self::perPage());
    }

    public static function today(): Paginator
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

    public static function forCalendar(Calendar $calendar): Paginator
    {
        return self::base()
            ->where('calendar_id', $calendar->id)
            ->paginate(self::perPage());
    }

    public static function forTag(string $tag): Paginator
    {
        return self::base()
            ->whereJsonContains('tags', $tag)
            ->paginate(self::perPage());
    }

    public static function search(string $search): Paginator
    {
        return self::base(hideChildren: false)
            ->where(fn (Builder $builder) => $builder
                ->orWhereRaw('lower(summary) like ? ', ['%'.$search.'%'])
                ->orWhereRaw('lower(description) like ? ', ['%'.$search.'%'])
                ->orWhereRaw('lower(tags) like ? ', ['%'.$search.'%']))
            ->paginate(self::perPage());
    }

    public static function lastModified(): Paginator
    {
        return Task::query()
            ->orderByDesc('updated_at')
            ->paginate(self::perPage());
    }

    public static function perPage(): int
    {
        return session('per-page', 15);
    }
}
