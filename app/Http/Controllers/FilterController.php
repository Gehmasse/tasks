<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FilterController extends Controller
{
    public function index(): View
    {
        return view('filters', [
            'filters' => Filter::all(),
        ]);
    }

    public function store(): RedirectResponse
    {
        $filter = new Filter;
        $filter->name = 'New Filter';
        $filter->filter = '[]';
        $filter->save();

        return redirect()->route('filters.show', $filter);
    }

    public function show(Filter $filter): View
    {
        return view('filter', ['filter' => $filter]);
    }

    public function update(Filter $filter): RedirectResponse
    {
        $filter->name = request('name');
        $filter->filter = request('filter');
        $filter->save();

        return back();
    }
}
