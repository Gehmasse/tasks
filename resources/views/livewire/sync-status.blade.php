<div wire:poll>
    @if($init->lessThan($this->latestUpdate()))
        <b>out of sync</b>
    @endif
</div>
