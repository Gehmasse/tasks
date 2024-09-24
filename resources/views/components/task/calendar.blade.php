<div class="list">
    <i class="bi bi-list-check"></i>

    <div class="line">
        <select name="calendar_id">
            @if($default() !== null)
                <option value="{{ $default()->id }}">{{ $default()->name }}</option>
            @endif

            @foreach($calendars() as $calendar)
                <option value="{{ $calendar->id }}">{{ $calendar->name }}</option>
            @endforeach
        </select>
    </div>
</div>
