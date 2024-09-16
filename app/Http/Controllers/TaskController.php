<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Tag;
use App\Models\Task;
use App\Tasks;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function all(): View
    {
        return view('tasks', [
            'title' => 'All Tasks',
            'tasks' => Tasks::all(),
        ]);
    }

    public function today(): View
    {
        return view('tasks', [
            'title' => 'Today',
            'tasks' => Tasks::today(),
        ]);
    }

    public function tomorrow(): View
    {
        return view('tasks', [
            'title' => 'Tomorrow',
            'tasks' => Tasks::tomorrow(),
        ]);
    }

    public function search(Request $request): View
    {
        $search = strtolower($request->get('search'));

        return view('tasks', [
            'title' => 'Search for "'.$search.'"',
            'tasks' => Tasks::search($search),
        ]);
    }

    public function lastModified(): View
    {
        return view('tasks', [
            'title' => 'Last Modified',
            'tasks' => Tasks::lastModified(),
        ]);
    }

    public function update(Task $task): RedirectResponse
    {
        $task->summary = request('summary', '');
        $task->due = ! empty(request('due-date'))
            ? ! empty(request('due-time'))
                ? Carbon::make(request('due-date').' '.request('due-time'))->format('Ymd\THis')
                : Carbon::make(request('due-date'))->format('Ymd')
            : '';
        $task->priority = request()->integer('priority');
        $task->tags = is_array(request('tags')) ? request('tags') : [];
        $task->description = request('description', '');

        $task->save();
        $task->upload();

        return back();
    }

    public function store(): RedirectResponse
    {
        $task = new Task;

        $uuid = (string) Str::uuid();
        $calendarId = request()->integer('calendar_id');

        $calendar = Calendar::find($calendarId);
        $now = now()->format('Ymd\THis');

        if ($calendar === null) {
            throw new ModelNotFoundException;
        }

        $task->calendar_id = $calendarId;
        $task->href = trim($calendar->href, '/').'/'.$uuid.'.ics';
        $task->etag = '';
        $task->ical = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//tasks.gehmasse.de//v1.0//
BEGIN:VTODO
DTSTAMP:'.$now.'
CREATED:'.$now.'
LAST-MODIFIED:'.$now.'
END:VTODO
END:VCALENDAR';
        $task->completed = false;
        $task->summary = request('summary', '');
        $task->uid = $uuid;
        $task->description = request('description', '');
        $task->due = ! empty(request('due-date'))
            ? ! empty(request('due-time'))
                ? Carbon::make(request('due-date').' '.request('due-time'))->format('Ymd\THis')
                : Carbon::make(request('due-date'))->format('Ymd')
            : '';
        $task->priority = request()->integer('priority');
        $task->tags = is_array(request('tags')) ? request('tags') : [];
        $task->parent_uid = '';

        $task->createAndUploadInitially();

        return redirect()->route('task', $task);
    }

    public function tag(Tag $tag): View
    {
        return view('tasks', [
            'title' => str_starts_with($tag, '@') ? 'Person '.$tag->name : 'Tag #'.$tag->name,
            'tasks' => Tasks::forTag($tag),
        ]);
    }
}
