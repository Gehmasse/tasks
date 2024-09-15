<?php

namespace App;

use App\Models\Calendar;
use App\Models\Task;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Tasks
{
    private static function base(bool $showCompleted = false, bool $hideChildren = true): Builder
    {
        $builder = Task::query();

        if ($showCompleted || ! session('completed', false)) {
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
            ->paginate();
    }

    public static function today(): Paginator
    {
        return self::base(showCompleted: true)
            ->where(fn (Builder $builder) => $builder
                ->whereNotNull('due')
                ->whereNot('due', '')
                ->where('due', '<', Carbon::tomorrow()->format('Ymd'))
                ->orWhere(fn (Builder $builder) => $builder
                    ->where('completed', true)
                    ->where('due', 'like', now()->startOfDay()->format('Ymd').'%')
                )
            )
            ->paginate();
    }

    public static function forCalendar(Calendar $calendar): Paginator
    {
        return self::base()
            ->where('calendar_id', $calendar->id)
            ->paginate();
    }

    public static function forTag(string $tag): Paginator
    {
        return self::base()
            ->whereJsonContains('tags', $tag)
            ->paginate();
    }

    public static function search(string $search): Paginator
    {
        return self::base(hideChildren: false)
            ->where(fn (Builder $builder) => $builder
                ->orWhereRaw('lower(summary) like ? ', ['%'.$search.'%'])
                ->orWhereRaw('lower(description) like ? ', ['%'.$search.'%'])
                ->orWhereRaw('lower(tags) like ? ', ['%'.$search.'%']))
            ->paginate();
    }

    public static function lastModified(): Paginator
    {
        return Task::query()
            ->orderByDesc('updated_at')
            ->paginate();
    }
}
