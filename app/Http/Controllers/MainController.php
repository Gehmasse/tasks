<?php

namespace App\Http\Controllers;

use App\Models\Remote;
use Illuminate\Http\RedirectResponse;

class MainController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        if (Remote::query()->count() === 0) {
            return redirect()->route('remotes');
        }

        return redirect()->route('tasks.today');
    }
}
