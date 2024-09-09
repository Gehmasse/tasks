<x-app>

    <h1>Calendars</h1>

    @forelse(App\Models\Remote::all() as $remote)
        <h2>{{ $remote->name }}</h2>

        @forelse($remote->calendars as $calendar)
            <div style="display: flex; flex-direction: column; gap: 10px; color: {{ $calendar->tasks->isEmpty() ? 'var(--col-light)' : 'var(--col)' }}">
                <a href="{{ route('calendar', $calendar) }}"><b>{{ $calendar->name }}:</b></a>
                <em>({{ $calendar->full_href }})</em>
                <span>{{ $calendar->ctag }}</span>
                <span>{{ $calendar->tasks->count() }} Tasks | {{ $calendar->tasks->where('completed', false)->count() }} Open</span>
            </div>
        @empty
            <b>No Calendars Found</b>
        @endforelse

    @empty
        <b>No Remotes Found</b>
    @endforelse

</x-app>
