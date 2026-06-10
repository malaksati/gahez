<div class="mb-3">
    <label class="form-label">{{ __('messages.From') }}</label>
    <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
</div>
<div class="mb-3">
    <label class="form-label">{{ __('messages.To') }}</label>
    <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
</div>


