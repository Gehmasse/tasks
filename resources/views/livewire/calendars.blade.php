<div class="calendar-list" wire:poll.5s>

    <a href="{{ route('sync-remote', $remote) }}" class="btn">Sync</a>
    <a href="{{ route('clear-remote', $remote) }}" class="btn">Clear</a>

    @forelse($remote->calendars as $calendar)
        <div class="calendar-details"
             style="color: {{ $calendar->tasks->isEmpty() ? 'var(--col-light)' : 'var(--col)' }}">
            <a href="{{ route('calendar', $calendar) }}"><b>{{ $calendar->name }}:</b></a>

            @if($calendar->default)
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

</div>
