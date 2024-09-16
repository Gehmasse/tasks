<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class FilterController extends Controller
{
    public function filters(): View
    {
        return view('filters', [
            'filters' => [
                'tasks.all' => 'All',
                'tasks.today' => 'Today',
                'tasks.tomorrow' => 'Tomorrow',
                'tasks.last-modified' => 'Last Modified',
            ],
        ]);
    }
}
