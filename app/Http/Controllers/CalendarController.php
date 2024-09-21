<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
        session(['calendar.default' => $calendar->id]);

        return back();
    }
}
