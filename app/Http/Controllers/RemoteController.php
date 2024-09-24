<?php

namespace App\Http\Controllers;

use App\Client;
use App\Jobs\SyncRemote;
use App\Models\Remote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RemoteController extends Controller
{
    public function index(): View
    {
        return view('remotes', ['remotes' => Remote::all() ]);
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
        Client::calendars($remote);
    }

    public function calendars(Remote $remote): View
    {
        return view('calendars', [
            'remote' => $remote,
            'calendars' => $remote->calendars,
        ]);
    }

    public function sync(Remote $remote): RedirectResponse
    {
        SyncRemote::dispatch($remote);

        return back();
    }
}
