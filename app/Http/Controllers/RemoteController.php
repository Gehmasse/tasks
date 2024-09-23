<?php

namespace App\Http\Controllers;

use App\Client;
use App\Models\Remote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RemoteController extends Controller
{
    public function index(): View
    {
        return view('remotes');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
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
    }

    public function update(Remote $remote): RedirectResponse
    {
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
    }

    public function check(Remote $remote): void
    {
        Client::new($remote)->calendars();
    }

    public function calendars(Remote $remote): View
    {
        return view('calendars', [
            'remote' => $remote,
            'calendars' => $remote->calendars,
        ]);
    }
}
