<x-app>

    <h1>Calendars for {{ $remote->name }}</h1>

    @forelse($calendars as $calendar)
        <div
            style="display: flex; flex-direction: column; gap: 10px; color: {{ $calendar->tasks->isEmpty() ? 'var(--col-light)' : 'var(--col)' }}">
            <a href="{{ route('calendar', $calendar) }}"><b>{{ $calendar->name }}:</b></a>

            @if(App\Models\Calendar::default()->id === $calendar->id)
                <b>This calendar is currently set as default.</b>
            @else
                <a href="{{ route('calendar.default', $calendar) }}">Set as default</a>
            @endif

            <em>({{ $calendar->full_href }})</em>
            <span>{{ $calendar->ctag }}</span>
            <span>{{ $calendar->tasks->count() }} Tasks | {{ $calendar->tasks->where('completed', false)->count() }} Open</span>
        </div>
    @empty
        <b>No Calendars Found</b>
    @endforelse

</x-app>
