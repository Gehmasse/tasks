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
