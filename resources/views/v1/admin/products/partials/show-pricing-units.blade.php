@php
    $units = $product->productUnits;
@endphp

@if ($units->isNotEmpty())
    <div class="table-responsive mt-3">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    @if ($product->isVariable())
                        <th>{{ __('messages.Variation') }}</th>
                    @endif
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
                @foreach ($units as $productUnit)
                    <tr>
                        @if ($product->isVariable())
                            <td>{{ $productUnit->variantLabel($locale) ?: '—' }}</td>
                        @endif
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
@endif
