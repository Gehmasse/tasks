<?php

use App\Client;
use App\Models\Calendar;
use App\Models\Remote;
use App\Models\Task;
use App\Tags;
use App\Tasks;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

Route::view('/', 'main')->name('main');

Route::view('/remotes', 'remotes')->name('remotes');

Route::post('/remotes', function () {
    $validated = request()->validate([
        'name' => 'required',
        'href' => 'required',
        'username' => 'required',
        'password' => 'required',
    ]);

    Remote::create([
        'name' => $validated['name'],
        'href' => $validated['href'],
        'username' => $validated['username'],
        'password' => $validated['password'],
    ]);

    return back();
})->name('remotes.store');

Route::post('/remotes/{remote}', function (Remote $remote) {
    $validated = request()->validate([
        'name' => 'required',
        'href' => 'required',
        'username' => 'required',
        'password' => 'required',
    ]);

    $remote->update([
        'name' => $validated['name'],
        'href' => $validated['href'],
        'username' => $validated['username'],
        'password' => $validated['password'],
    ]);

    return back();
})->name('remotes.update');

Route::get('/remotes/{remote}/check', function (Remote $remote) {
    Client::new($remote)->calendars();
})->name('remotes.check');

Route::view('/calendars', 'calendars')->name('calendars');

Route::get('/calendars/{calendar}', fn (Calendar $calendar) => view('tasks', [
    'title' => 'Calendar '.$calendar->name,
    'tasks' => Tasks::forCalendar($calendar),
]))->name('calendar');

Route::get('/filters', fn () => view('filters', [
    'filters' => [
        'tasks.all' => 'All',
        'tasks.today' => 'Today',
        'tasks.last-modified' => 'Last Modified',
    ],
]))->name('filters');

Route::get('/tasks/all', fn () => view('tasks', [
    'title' => 'All Tasks',
    'tasks' => Tasks::all(),
]))->name('tasks.all');

Route::get('/tasks/today', fn () => view('tasks', [
    'title' => 'Today',
    'tasks' => Tasks::today(),
]))->name('tasks.today');

Route::post('/tasks/search', fn () => redirect()->route('tasks.search.get', strtolower(request('search'))))
    ->name('tasks.search');

Route::get('/tasks/search/{search}', fn (string $search) => view('tasks', [
    'title' => 'Search for "'.$search.'"',
    'tasks' => Tasks::search($search),
]))->name('tasks.search.get');

Route::get('/tasks/last-modified', fn () => view('tasks', [
    'title' => 'Last Modified',
    'tasks' => Tasks::lastModified(),
]))->name('tasks.last-modified');

Route::post('/tasks/{task}/complete', function (Task $task) {
    $task->completed = request()->boolean('complete');

    $task->save();

    $task->upload();

    return $task->completed
        ? 'Task '.$task->id.' is completed'
        : 'Task '.$task->id.' is not completed anymore';
})->name('tasks.complete');

Route::get('/tasks/{task}', fn (Task $task) => view('task-full', ['task' => $task]))->name('task');

Route::put('/tasks/{task}', function (Task $task) {
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
})->name('task.update');

Route::view('/search', 'search')->name('search');

Route::get('/tags', fn () => view('tags', ['tags' => Tags::all()]))->name('tags');

Route::get('/tags/{tag}', fn (string $tag) => view('tasks', [
    'title' => 'Tag #'.$tag,
    'tasks' => Tasks::forTag($tag),
]))->name('tag');

Route::any('/sync', Client::syncNextPart(...))->name('sync');

Route::any('/set', function () {
    if (request()->exists('completed')) {
        session(['completed' => request()->boolean('completed')]);
    }

    if (request()->exists('show-all')) {
        session(['show-all' => request()->boolean('show-all')]);
    }

    return back();
})->name('set');

Route::view('/settings', 'settings')->name('settings');

Route::get('/cache-all', function () {
    Task::all()->each(fn (Task $task) => $task->save());

    return back();
})->name('cache-all');
