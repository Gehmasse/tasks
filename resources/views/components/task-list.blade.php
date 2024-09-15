@php /** @var Illuminate\Contracts\Pagination\Paginator $tasks */ @endphp

<div class="table-controls">

    <form action="{{ route('set') }}" class="task-list-settings">
        <div>
            <label>With completed:</label>
            <input type="checkbox" name="completed" {{ session('completed', false) ? 'checked' : '' }}>
        </div>

        <div>
            <label>Tasks per page:</label>
            <input type="number" name="per-page" size="3" value="{{ App\Tasks::perPage() }}">
        </div>

        <input type="submit" value="Set">
    </form>

    <div class="pagination-controls">
        @if($tasks->currentPage() > 1)
            <div><a href="{{ $tasks->url(1) }}">&lt;&lt;</a></div>
            <div><a href="{{ $tasks->previousPageUrl() }}">&lt;</a></div>
        @endif

        <div>page {{ $tasks->currentPage() }} of {{ $tasks->lastPage() }}</div>

        @if($tasks->hasMorePages())
            <div><a href="{{ $tasks->nextPageUrl() }}">&gt;</a></div>
            <div><a href="{{ $tasks->url($tasks->lastPage()) }}">&gt;&gt;</a></div>
        @endif
    </div>
</div>

<div class="tasks">
    @forelse($tasks as $task)
        <livewire:task-inline :task="$task"/>
    @empty
        <b>No Tasks Found</b>
    @endforelse
</div>

