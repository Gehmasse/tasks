<?php

namespace App\View\Components\Task;

use App\Models\Calendar as CalendarModel;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Calendar extends Component
{
    public function __construct() {}

    public function default(): ?CalendarModel
    {
        return once(fn () => CalendarModel::default());
    }

    public function calendars(): Collection
    {
        return CalendarModel::all();
    }

    public function render(): View
    {
        return view('components.task.calendar');
    }
}
