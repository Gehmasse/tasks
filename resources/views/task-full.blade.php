@php /** @var App\Models\Task $task */ @endphp

<x-app>

    <h1>Task #{{ $task->id }}</h1>

    <form action="{{ route('task.update', $task) }}" class="task-full" method="post">

        @csrf

        <div class="summary grow-wrap">
            <textarea class="summary" name="summary">{{ $task->summary }}</textarea>
        </div>

        <div class="due">
            <i class="bi bi-clock"></i>

            <div class="line">
                <div class="removable">
                    <input type="date" name="due-date"
                           value="{{ $task->due_carbon?->format('Y-m-d') }}">
                    <div class="remove-btn">&times;</div>
                </div>

                <div class="removable">
                    <input type="time" name="due-time"
                           value="{{ $task->hasDueTime() ? $task->due_carbon?->format('H:i') : '' }}">
                    <div class="remove-btn">&times;</div>
                </div>
            </div>
        </div>

        <div class="priority">
            <i class="bi bi-flag"></i>

            <x-priority :priority="$task->priority()"/>
        </div>

        <div class="tags">
            <i class="bi bi-tags"></i>

            <livewire:tag-picker :tags="$task->tagObjects()->pluck('id')->toArray()" />
        </div>

        <div class="description">
            <i class="bi bi-text-left"></i>

            <div class="grow-wrap">
                <textarea name="description">{{ $task->description }}</textarea>
            </div>
        </div>

        <input type="submit" value="Save">

    </form>

    <script>
        function setVariableInputLength() {
            document.querySelectorAll('.variable-input-length').forEach(elem => {
                const action = () => {
                    elem.size = Math.max(elem.value.length, 5)
                }

                elem.addEventListener('blur', action)
                elem.addEventListener('change', action)
                elem.addEventListener('keyup', action)
                action()
            })
        }

        document.querySelectorAll('.grow-wrap').forEach(grower => {
            const textarea = grower.querySelector('textarea')

            textarea.addEventListener('input', () => {
                grower.dataset.replicatedValue = textarea.value
            })

            grower.dataset.replicatedValue = textarea.value
        })

        document.querySelector('[name="due-date"] ~ .remove-btn').addEventListener('click', () => {
            document.querySelector('[name="due-date"]').value = undefined
            document.querySelector('[name="due-time"]').value = undefined
        })

        document.querySelector('[name="due-time"] ~ .remove-btn').addEventListener('click', () => {
            document.querySelector('[name="due-time"]').value = undefined
        })

        setVariableInputLength()
    </script>

</x-app>
