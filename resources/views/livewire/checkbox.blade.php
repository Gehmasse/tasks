<div class="checkbox">
    <input wire:model.live="checked"
           type="checkbox"
           id="{{ $this->id() }}"
           name="{{ $this->id() }}">

    <label class="wrapper" for="{{ $this->id() }}">
        <span>&times;</span>
    </label>
</div>
