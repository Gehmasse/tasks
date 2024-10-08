<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Filter;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function all(): View
    {
        return view('tasks', [
            'title' => 'All Tasks',
            'filter' => 'all',
        ]);
    }

    public function today(): View
    {
        return view('tasks', [
            'title' => 'Today',
            'filter' => 'today',
        ]);
    }

    public function tomorrow(): View
    {
        return view('tasks', [
            'title' => 'Tomorrow',
            'filter' => 'tomorrow',
        ]);
    }

    public function search(Request $request): View
    {
        $search = strtolower($request->get('search'));

        return view('tasks', [
            'title' => 'Search for "'.$search.'"',
            'filter' => 'search',
            'params' => [$search],
        ]);
    }

    public function lastModified(): View
    {
        return view('tasks', [
            'title' => 'Last Modified',
            'filter' => 'lastModified',
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
        $task->tags = $this->tags();
        $task->description = request('description', '');

        $task->save();
        $task->upload();

        return Redirect::route('tasks')->success('Updated task '.$task->id);
    }

    public function store(): RedirectResponse
    {
        $task = new Task;

        $uuid = (string) Str::uuid();
        $calendarId = request()->integer('calendar_id');

        $calendar = Calendar::query()->findOrFail($calendarId);
        $now = now()->format('Ymd\THis');

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
        $task->tags = $this->tags();
        $task->parent_uid = '';

        $task->createAndUploadInitially();

        return Redirect::route('tasks')->success('Created task '.$task->id);
    }

    public function tag(Tag $tag): View
    {
        return view('tasks', [
            'title' => str_starts_with($tag, '@') ? 'Person '.$tag->name : 'Tag #'.$tag->name,
            'filter' => 'forTag',
            'params' => [$tag],
        ]);
    }

    public function tags(): array
    {
        return is_array(request('tags'))
            ? array_map(fn (mixed $id) => Tag::find($id)->name, request('tags'))
            : [];
    }

    public function filter(Filter $filter): View
    {
        return view('tasks', [
            'title' => 'Filter',
            'filter' => $filter->id,
        ]);
    }
}
