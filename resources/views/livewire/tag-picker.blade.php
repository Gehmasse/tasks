<div class="tag-picker">
    <label class="new-tag">
        <input class="tag-input" size="20" wire:model.live="tagInput">
        <span wire:click="newTag" class="add-btn"><i class="bi bi-plus-circle-fill"></i></span>
    </label>

    <div class="list">
        @foreach($this->allTags() as $key => $tag)
            <label class="tag tag-edit"
                   style="background-color: {{ $tag->color }}"
                   wire:key="$key">

                <input id="tag-{{ $tag->id }}"
                       type="checkbox"
                       value="{{ $tag->id }}"
                       wire:model.live="tags"
                       name="tags[]">
                <i class="bi {{ $tag->icon }}"></i> {{ $tag->nameWithoutPrefix() }}

                <label for="tag-{{ $tag->id }}" class="tag-btn remove-btn">&times;</label>
            </label>
        @endforeach
    </div>

    <script>
        document.querySelector('.tag-input').addEventListener('keyup', () => {
            const search = document.querySelector('.tag-input').value

            let visible = 0

            document.querySelectorAll('.tag-edit').forEach(elem => {
                if (search.trim() !== '' && elem.innerText.includes(search)) {
                    elem.classList.add('hidden')
                } else {
                    elem.classList.remove('hidden')
                    visible++
                }
            })
        })
    </script>
</div>
