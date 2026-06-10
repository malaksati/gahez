@php
    $locale = app()->getLocale();
@endphp

@if ($product->isVariable())
    @php
        $variants = $product->relationLoaded('variants') ? $product->variants : collect();
        $availableVariants = $variants->filter(fn ($variant) => $variant->isInStock());
    @endphp
    @if ($availableVariants->isEmpty())
        <span class="badge bg-danger">{{ __('messages.Out of stock') }}</span>
    @else
        @php
            $trackedAvailable = $availableVariants->filter(fn ($variant) => $variant->tracksStock());
        @endphp
        @if ($trackedAvailable->isEmpty())
            <span class="badge bg-success">{{ __('messages.Available') }}</span>
        @else
            <span class="badge bg-success">{{ (int) $trackedAvailable->sum('stock') }}</span>
        @endif
    @endif
@else
    @if ($product->isInStock())
        @if ($product->tracksStock())
            <span class="badge bg-success">{{ (int) $product->stock }}</span>
        @else
            <span class="badge bg-success">{{ __('messages.Available') }}</span>
        @endif
    @else
        <span class="badge bg-danger">{{ __('messages.Out of stock') }}</span>
    @endif
@endif
