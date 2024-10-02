<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CalendarController extends Controller
{
    public function index(Calendar $calendar): View
    {
        return view('tasks', [
            'title' => 'Calendar '.$calendar->name,
            'filter' => 'forCalendar',
            'params' => [$calendar],
        ]);
    }

    public function default(Calendar $calendar): RedirectResponse
    {
        Calendar::default($calendar);

        return back();
    }
}
