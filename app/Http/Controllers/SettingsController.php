<?php

namespace App\Http\Controllers;

use App\Client;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    public function sync(): JsonResponse
    {
        return Client::syncNextPart();
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
}
