<x-app>

    <h1>Create New Task</h1>

    <form action="{{ route('task.store') }}" class="task-full" method="post">

        @csrf

        <x-task.calendar/>
        <x-task.summary/>
        <x-task.due/>
        <x-task.priority/>
        <x-task.tags/>
        <x-task.description/>

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

        addRemoveEvents()
        setVariableInputLength()
    </script>

</x-app>
