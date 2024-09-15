<x-app>

    <h1>Create New Task</h1>

    <form id="new-tag-form"></form>

    <form action="{{ route('task.store') }}" class="task-full" method="post">

        @csrf

        <select name="calendar_id">
            @if(($default = App\Models\Calendar::default()) !== null)
                <option value="{{ $default->id }}">{{ $default->name }}</option>
            @endif

            @foreach(App\Models\Calendar::all() as $calendar)
                <option value="{{ $calendar->id }}">{{ $calendar->name }}</option>
            @endforeach
        </select>

        <div class="summary grow-wrap">
            <textarea class="summary" name="summary"></textarea>
        </div>

        <div class="due">
            <i class="bi bi-clock"></i>

            <div class="line">
                <div class="removable">
                    <input type="date" name="due-date" value="{{ now()->format('Y-m-d') }}">
                    <div class="remove-btn">&times;</div>
                </div>

                <div class="removable">
                    <input type="time" name="due-time">
                    <div class="remove-btn">&times;</div>
                </div>
            </div>
        </div>

        <div class="priority">
            <i class="bi bi-flag"></i>

            <x-priority :priority="new App\Priority(9)"/>
        </div>

        <div class="tags">
            <i class="bi bi-tags"></i>

            <div class="line">
                <div id="current-tags"></div>

                <div style="white-space: nowrap">
                    <input form="new-tag-form" id="new-tag-input" class="variable-input-length" list="tags">
                    <input form="new-tag-form" id="new-tag-action" type="submit" value="+">
                </div>

                <datalist id="tags">
                    {{--                    @foreach(App\Tags::all() as $tag)--}}
                    {{--                        <option value="{{ $tag }}">{{ $tag }}</option>--}}
                    {{--                    @endforeach--}}
                </datalist>
            </div>
        </div>

        <div class="description">
            <i class="bi bi-text-left"></i>

            <div class="grow-wrap">
                <textarea name="description"></textarea>
            </div>
        </div>

        <input type="submit" value="Save">

    </form>

    <script>
        function tag(name) {
            return `<div class="tag-edit">
                <input class="tag-input variable-input-length" value="${name}" name="tags[]">
                ${removeBtn()}
            </div>`
        }

        function removeBtn() {
            return '<div class="remove-btn">&times;</div>'
        }

        function addRemoveEvents() {
            document.querySelectorAll('.tag-edit .remove-btn').forEach(elem => {
                elem.addEventListener('click', () => {
                    elem.parentElement.remove()
                })
            })
        }

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


        document.querySelector('#new-tag-form').addEventListener('submit', e => {
            e.preventDefault()

            const currentTags = document.querySelector('#current-tags');
            const newTag = document.querySelector('#new-tag-input').value;
            document.querySelector('#new-tag-input').value = ''

            if (newTag === '') {
                return
            }

            currentTags.innerHTML += tag(newTag)

            addRemoveEvents()
            setVariableInputLength()
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
