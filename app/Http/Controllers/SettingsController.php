<?php

namespace App\Http\Controllers;

use App\Jobs\SyncAll;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Native\Laravel\Facades\Shell;
use Native\Laravel\Facades\Window;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings');
    }

    public function set(): RedirectResponse
    {
        session([
            'completed' => request()->boolean('completed'),
            'per-page' => request()->integer('per-page'),
        ]);

        return back();
    }

    public function cacheAll(): RedirectResponse
    {
        Task::all()->each(fn (Task $task) => $task->save());

        return back();
    }

    public function logs(): RedirectResponse
    {
        Window::open('logs')
            ->width(1000)
            ->height(800)
            ->route('log-viewer.index');

        return back();
    }

    public function folder(): RedirectResponse
    {
        Shell::showInFolder(storage_path());

        return back();
    }

    public function sync(): RedirectResponse
    {
        logger()->log('info', 'starting sync...');

        SyncAll::dispatch();

        return back();
    }
}
