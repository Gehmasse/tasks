<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\RemoteController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Models\Task;
use Illuminate\Support\Facades\Route;

Route::view('/', 'main')->name('main');

Route::view('remotes', 'remotes')->name('remotes');
Route::post('remotes', [RemoteController::class, 'store'])->name('remotes.store');
Route::post('remotes/{remote}', [RemoteController::class, 'update'])->name('remotes.update');
Route::get('remotes/{remote}/check', [RemoteController::class, 'check'])->name('remotes.check');
Route::get('remotes/{remote}/calendars', [RemoteController::class, 'calendars'])->name('calendars');

Route::get('calendars/{calendar}', [CalendarController::class, 'index'])->name('calendar');
Route::any('calendars/{calendar}/default', [CalendarController::class, 'default'])->name('calendar.default');

Route::get('filters', [FilterController::class, 'filters'])->name('filters');

Route::get('tasks', [TaskController::class, 'today'])->name('tasks');
Route::get('tasks/all', [TaskController::class, 'all'])->name('tasks.all');
Route::get('tasks/today', [TaskController::class, 'today'])->name('tasks.today');
Route::get('tasks/tomorrow', [TaskController::class, 'tomorrow'])->name('tasks.tomorrow');
Route::get('tasks/search', [TaskController::class, 'search'])->name('tasks.search');
Route::get('tasks/last-modified', [TaskController::class, 'lastModified'])->name('tasks.last-modified');
Route::get('tasks/{filter}', [TaskController::class, 'filter'])->name('tasks.filter');

Route::get('filters', [FilterController::class, 'index'])->name('filters');
Route::post('filters', [FilterController::class, 'store'])->name('filters.store');
Route::get('filters/{filter}', [FilterController::class, 'show'])->name('filters.show');
Route::post('filters/{filter}', [FilterController::class, 'update'])->name('filters.update');

Route::get('tasks/create', fn () => view('task-create'))->name('task.create');
Route::get('tasks/{task}', fn (Task $task) => view('task-full', ['task' => $task]))->name('task');

Route::post('tasks', [TaskController::class, 'store'])->name('task.store');
Route::post('tasks/{task}', [TaskController::class, 'update'])->name('task.update');

Route::view('search', 'search')->name('search');

Route::get('tags', [TagController::class, 'tags'])->name('tags');
Route::get('people', [TagController::class, 'people'])->name('people');
Route::get('tags/{tag}/tasks', [TaskController::class, 'tag'])->name('tag');

Route::any('sync', [SettingsController::class, 'sync'])->name('sync');
Route::any('set', [SettingsController::class, 'set'])->name('set');
Route::get('cache-all', [SettingsController::class, 'cacheAll'])->name('cache-all');
