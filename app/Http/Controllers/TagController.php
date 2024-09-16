<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    public function tags(): View
    {
        return view('tags', [
            'title' => 'Tags',
            'tags' => Tag::allTags(),
        ]);
    }

    public function people(): View
    {
        return view('tags', [
            'title' => 'People',
            'tags' => Tag::allPeople(),
        ]);
    }
}
