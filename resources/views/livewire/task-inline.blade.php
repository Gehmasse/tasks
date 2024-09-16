@php /** @var App\Models\Task $task */ @endphp

<div class="task-with-children">
    @if($task->children->isNotEmpty())
        <div class="open-close" wire:click="toggleChildren">{{ $showChildren ? '-' : '+' }}</div>
    @endif

    <div class="task {{ $task->priority()->color() }}" style="margin-left: {{ $indent * 40 }}px">

        <livewire:checkbox wire:model.live="completed"/>

        <div class="stack">
            <div class="line">
                <div class="left">
                    <div>{{ $task->summary }}</div>
                    <em class="due" @style(['color: red' => $task->due_carbon?->isBefore(today())])>{{ $task->due_formatted }}</em>

                    @foreach($task->tags as $tag)
                        <x-tag :tag="App\Models\Tag::findByName($tag)"/>
                    @endforeach

                    @if($indent === 0 && $task->parent !== null)
                        Parent: {{ $task->parent->summary }}
                    @endif
                </div>

                <a class="link" href="{{ route('task', $task) }}">#{{ $task->id }}</a>
            </div>

            @if($task->description !== '')
                <div class="description">
                    {{ $task->description }}
                </div>
            @endif

        </div>
    </div>

    @if($showChildren)
        @foreach($task->children as $child)
            <livewire:task-inline :task="$child" :indent="$indent + 1"/>
        @endforeach
    @endif
</div>
