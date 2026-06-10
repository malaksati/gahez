@props([
    'col' => 'col-md-4',
    'placeholder' => '',
    'value' => request('search'),
])

<div class="{{ $col }}">
    <label class="form-label small mb-1">{{ __('messages.Search') }}</label>
    <input
        type="search"
        name="search"
        value="{{ $value }}"
        class="form-control form-control-sm"
        placeholder="{{ $placeholder }}"
        data-auto-search
        autocomplete="off"
    >
</div>
