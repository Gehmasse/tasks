@php /** @var App\Models\Task $task */ @endphp

<div class="task {{ $task->color }}" style="margin-left: {{ $indent * 40 }}px">
    <x-checkbox :task="$task"/>

    <div class="stack">
        <div class="line">
            <div>{{ $task->summary }}</div>
            <em class="due" @style(['color: red' => $task->due_carbon?->isBefore(today())])>{{ $task->due }}</em>

            @foreach($task->tags as $tag)
                <x-tag :tag="$tag"/>
            @endforeach

            @if($indent === 0 && $task->parent !== null)
                Parent: {{ $task->parent->summary }}
            @endif
        </div>

        @if($task->description !== '')
            <div>
                {{ $task->description }}
            </div>
        @endif

    </div>
</div>

@foreach($task->children as $child)
    <x-task :task="$child" :indent="$indent + 1"/>
@endforeach
