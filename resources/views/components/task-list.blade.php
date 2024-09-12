@php /** @var Illuminate\Contracts\Pagination\Paginator $tasks */ @endphp

<div class="table-controls">

    <x-toggle
        :checked="session('completed', false)"
        :on="route('set', ['completed' => 1])"
        :off="route('set', ['completed' => 0])"
        color="blue"
        label="show completed"
    />

{{--    <x-toggle--}}
{{--        :checked="session('show-all', false)"--}}
{{--        :on="route('set', ['show-all' => 1])"--}}
{{--        :off="route('set', ['show-all' => 0])"--}}
{{--        color="green"--}}
{{--        label="show all"--}}
{{--    />--}}

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
        <x-task-inline :$task/>
    @empty
        <b>No Tasks Found</b>
    @endforelse
</div>

