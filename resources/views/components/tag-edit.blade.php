<div class="tag-edit">
    <input type="text"
           class="tag-input variable-input-length"
           value="{{ $tag->name }}"
           name="tags[]"
           style="background: {{ $tag->color }}">
    <div class="remove-btn">&times;</div>
</div>
