<?php

use App\Client;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\RemoteController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Models\Calendar;
use App\Models\Task;
use App\Tags;
use App\Tasks;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::view('/', 'main')->name('main');

Route::view('/remotes', 'remotes')->name('remotes');

Route::post('/remotes', [RemoteController::class, 'store'])->name('remotes.store');
Route::post('/remotes/{remote}', [RemoteController::class, 'update'])->name('remotes.update');
Route::get('/remotes/{remote}/check', [RemoteController::class, 'check'])->name('remotes.check');

Route::view('/calendars', 'calendars')->name('calendars');

Route::get('/calendars/{calendar}', [CalendarController::class, 'index'])->name('calendar');
Route::any('/calendars/{calendar}/default', [CalendarController::class, 'default'])->name('calendar.default');

Route::get('/filters', fn () => view('filters', [
    'filters' => [
        'tasks.all' => 'All',
        'tasks.today' => 'Today',
        'tasks.last-modified' => 'Last Modified',
    ],
]))->name('filters');

Route::get('/tasks/all', [TaskController::class, 'all'])->name('tasks.all');
Route::get('/tasks/today', [TaskController::class, 'today'])->name('tasks.today');
Route::get('/tasks/tomorrow', [TaskController::class, 'tomorrow'])->name('tasks.tomorrow');
Route::get('/tasks/search', [TaskController::class, 'search'])->name('tasks.search');
Route::get('/tasks/last-modified', [TaskController::class, 'lastModified'])->name('tasks.last-modified');

Route::get('/tasks/create', fn () => view('task-create'))->name('task.create');
Route::get('/tasks/{task}', fn (Task $task) => view('task-full', ['task' => $task]))->name('task');

Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('task.update');
Route::post('/tasks', [TaskController::class, 'store'])->name('task.store');

Route::view('/search', 'search')->name('search');

Route::get('/tags', [TagController::class, 'tags'])->name('tags');
Route::get('/people', [TagController::class, 'people'])->name('people');
Route::get('/tags/{tag}', [TaskController::class, 'tag'])->name('tag');

Route::any('/sync', function () {
    return Client::syncNextPart();
})->name('sync');

Route::any('/set', function () {
    session([
        'completed' => request()->boolean('completed'),
        'per-page' => request()->integer('per-page'),
    ]);

    return back();
})->name('set');

Route::get('/cache-all', function () {
    Task::all()->each(fn (Task $task) => $task->save());

    return back();
})->name('cache-all');
