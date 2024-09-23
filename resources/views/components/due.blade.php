@php /** @var \App\Models\Task $task */ @endphp

<em class="due" @style(['color: red' => $task->due_carbon?->isBefore(today())])>{{ $task->due_formatted }}</em>
