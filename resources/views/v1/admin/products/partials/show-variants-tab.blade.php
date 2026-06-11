@php
    $variantUnits = $product->productUnits->where('is_active', true);
@endphp

@if ($product->variants->isEmpty())
    <p class="text-muted mb-0">{{ __('messages.No variant rows yet. Add at least one.') }}</p>
@else
    <p class="text-muted small mb-3">{{ __('messages.Variant units show hint') }}</p>

    @foreach ($product->variants as $variant)
        @php
            $optionSummary = $variant->values
                ->sortBy(fn ($value) => $value->variantOption?->variant_id ?? 0)
                ->map(function ($value) use ($locale) {
                    $attribute = $value->variantOption?->variant?->getTranslation('name', $locale, false)
                        ?: $value->variantOption?->variant?->getTranslation('name', 'en');
                    $optionName = $value->variantOption?->getTranslation('name', $locale, false)
                        ?: $value->variantOption?->getTranslation('name', 'en');

                    return trim(($attribute ? $attribute.': ' : '').$optionName);
                })
                ->filter()
                ->implode(' · ');

            $variantThumbnailPath = $variant->getRawOriginal('thumbnail');
            $variantThumbnailUrl = $variantThumbnailPath
                ? asset('storage/'.$variantThumbnailPath)
                : null;

            $unitsForVariant = $variant->relationLoaded('productUnits')
                ? $variant->productUnits->where('is_active', true)
                : $variantUnits->where('product_variant_id', $variant->id);

            $variantName = $variant->getTranslation('name', $locale, false)
                ?: $variant->getTranslation('name', 'en')
                ?: $optionSummary;
        @endphp

        <div class="border rounded mb-3">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap align-items-start gap-3 mb-3">
                    @if ($variantThumbnailUrl)
                        <img src="{{ $variantThumbnailUrl }}" alt=""
                            class="img-thumbnail flex-shrink-0"
                            style="width: 56px; height: 56px; object-fit: cover;">
                    @endif
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $variantName }}</h6>
                        @if ($optionSummary !== '')
                            <div class="small text-muted mb-1">{{ $optionSummary }}</div>
                        @endif
                        <div class="small">
                            <span class="text-muted">{{ __('messages.SKU') }}:</span>
                            <code>{{ $variant->sku ?: '—' }}</code>
                            <span class="mx-2">·</span>
                            @include('v1.admin.partials.active-badge', ['active' => $variant->is_active])
                        </div>
                    </div>
                </div>

                @if ($unitsForVariant->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.Unit') }}</th>
                                    <th>{{ __('messages.SKU') }}</th>
                                    <th>{{ __('messages.Unit factor') }}</th>
                                    <th>{{ __('messages.Price') }}</th>
                                    <th>{{ __('messages.Discount value') }}</th>
                                    <th>{{ __('messages.Final price') }}</th>
                                    <th>{{ __('messages.Stock') }}</th>
                                    <th>{{ __('messages.Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($unitsForVariant as $productUnit)
                                    <tr>
                                        <td>
                                            {{ $productUnit->displayUnitName($locale) ?: '—' }}
                                            @if ($productUnit->is_default)
                                                <span class="badge bg-primary ms-1">{{ __('messages.Default') }}</span>
                                            @endif
                                        </td>
                                        <td><code class="small">{{ $productUnit->sku ?: '—' }}</code></td>
                                        <td>{{ $productUnit->factor }}</td>
                                        <td>{{ format_local_number((float) $productUnit->price, 2) }}{{ $currency ? ' '.$currency : '' }}</td>
                                        <td>
                                            @if ($productUnit->discount > 0)
                                                @if ($productUnit->discount_type === 'percentage')
                                                    @num($productUnit->discount)%
                                                @else
                                                    {{ format_local_number((float) $productUnit->discount, 2) }}{{ $currency ? ' '.$currency : '' }}
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-success fw-semibold">
                                            {{ format_local_number($productUnit->final_price, 2) }}{{ $currency ? ' '.$currency : '' }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $productUnit->isInStock() ? 'bg-success' : 'bg-danger' }}">
                                                @if ($productUnit->tracksStock())
                                                    @num($productUnit->stock)
                                                @else
                                                    {{ $productUnit->is_in_stock ? __('messages.Available') : __('messages.Out of stock') }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>@include('v1.admin.partials.active-badge', ['active' => $productUnit->is_active])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small mb-0">{{ __('messages.No sellable units for this variation') }}</p>
                @endif
            </div>
        </div>
    @endforeach
@endif
