<div class="priority-line">
    <div class="radio">
        <input type="radio" id="priority-0" name="priority" value="0" {{ $task->priority()->none() ? ' checked' : '' }}>
        <label for="priority-0" class="priority-0"></label>
    </div>

    <div class="radio">
        <input type="radio" id="priority-1" name="priority" value="1" {{ $task->priority()->low() ? ' checked' : '' }}>
        <label for="priority-1" class="priority-1"></label>
    </div>

    <div class="radio">
        <input type="radio" id="priority-5" name="priority" value="5" {{ $task->priority()->mid() ? ' checked' : '' }}>
        <label for="priority-5" class="priority-5"></label>
    </div>

    <div class="radio">
        <input type="radio" id="priority-9" name="priority" value="9" {{ $task->priority()->high() ? ' checked' : '' }}>
        <label for="priority-9" class="priority-9"></label>
    </div>
</div>
