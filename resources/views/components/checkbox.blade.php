<div class="checkbox {{ $task->completed ? 'checked' : '' }}"
     data-url="{{ route('tasks.complete', $task) }}">
    <label>&times;</label>
</div>
