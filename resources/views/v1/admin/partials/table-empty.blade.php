@props([
    'icon' => 'inbox',
    'message',
    'createUrl' => null,
    'createLabel' => null,
])

<div class="text-center py-5">
    <i class="bi bi-{{ $icon }} fs-1 text-muted"></i>
    <p class="text-muted mt-3 mb-0">{{ $message }}</p>
    @if ($createUrl && $createLabel)
        <a href="{{ $createUrl }}" class="btn btn-primary mt-3">
            <i class="bi bi-plus-lg me-2"></i>{{ $createLabel }}
        </a>
    @endif
</div>
