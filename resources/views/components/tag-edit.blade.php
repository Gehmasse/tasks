<div class="tag-edit">
    <input type="text"
       class="tag-input variable-input-length"
       value="{{ $tag }}"
       name="tags[]"
       style="background: {{ App\Tags::color($tag) }}">
    <div class="remove-btn">&times;</div>
</div>
