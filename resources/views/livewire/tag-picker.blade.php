<div class="tag-picker">
    <label><input class="tag-input" wire:model.live="tagInput"></label>

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

    <script>
        document.querySelector('.tag-input').addEventListener('keyup', () => {
            const search = document.querySelector('.tag-input').value

            document.querySelectorAll('.tag-edit').forEach(elem => {
                if (search.trim() !== '' && elem.innerText.includes(search)) {
                    elem.classList.add('hidden')
                } else {
                    elem.classList.remove('hidden')
                }
            })
        })
    </script>
</div>
