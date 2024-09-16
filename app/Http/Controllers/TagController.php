<?php

namespace App\Http\Controllers;

use App\Tags;
use Illuminate\View\View;

class TagController extends Controller
{
    public function tags(): View
    {
        return view('tags', [
            'title' => 'Tags',
            'tags' => Tags::all(),
        ]);
    }

    public function people(): View
    {
        return view('tags', [
            'title' => 'People',
            'tags' => Tags::allPeople(),
        ]);
    }
}
