@php
    $locale = app()->getLocale();
    $currency = app_currency();
@endphp

@if ($products->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Image') }}</th>
                    <th>{{ __('messages.Name') }}</th>
                    @if (! empty($showCategoriesColumn))
                        <th>{{ __('messages.Categories') }}</th>
                    @endif
                    <th>{{ __('messages.Brand') }}</th>
                    <th>{{ __('messages.SKU') }}</th>
                    <th>{{ __('messages.Unit') }}</th>
                    <th>{{ __('messages.Price') }}</th>
                    <th>{{ __('messages.Stock') }}</th>
                    <th>{{ __('messages.Type') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Featured') }}</th>
                    <th>{{ __('messages.Approved') }}</th>
                    <th>{{ __('messages.Created') }}</th>
                    <th class="text-end">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    @php
                        $nameLocale = $product->getTranslation('name', $locale, false) ?: $product->getTranslation('name', 'en');
                        $brandName = $product->displayBrandName($locale) ?? '';
                        $searchText = mb_strtolower(implode(' ', array_filter([
                            $nameLocale,
                            $product->getTranslation('name', 'en'),
                            $product->getTranslation('name', 'ar'),
                            $product->sku,
                            $brandName,
                        ])));
                    @endphp
                    <tr data-product-row="{{ $product->id }}" data-product-search="{{ $searchText }}">
                        <td>
                            <img
                                src="{{ $product->thumbnail }}"
                                alt="{{ $nameLocale }}"
                                class="img-thumbnail"
                                style="width: 50px; height: 50px; object-fit: cover;"
                            >
                        </td>
                        <td>
                            <strong>{{ $nameLocale }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $product->getTranslation('name', 'en') }} / {{ $product->getTranslation('name', 'ar') }}
                            </small>
                        </td>
                        @if (! empty($showCategoriesColumn))
                            <td>
                                @if ($product->categories->isNotEmpty())
                                    @foreach ($product->categories as $category)
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            {{ $category->getTranslation('name', $locale, false) ?: $category->getTranslation('name', 'en') }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="badge bg-warning text-dark">{{ __('messages.Uncategorized') }}</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            @php($brandName = $product->displayBrandName($locale))
                            @if ($brandName)
                                <span class="badge bg-info text-dark">{{ $brandName }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <code>{{ $product->sku }}</code>
                        </td>
                        <td>
                            @if ($product->formattedUnitsSummary($locale))
                                <span>{{ $product->formattedUnitsSummary($locale) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ number_format((float) $product->price, 2) }}{{ $currency ? ' '.$currency : '' }}</strong>
                            @if ($product->hasDiscount())
                                <br>
                                <small class="text-success">
                                    {{ number_format($product->final_price, 2) }}{{ $currency ? ' '.$currency : '' }}
                                </small>
                            @endif
                        </td>
                        <td>
                            @include('v1.admin.products.partials.stock-badge', ['product' => $product])
                        </td>
                        <td>
                            @if ($product->type === 'variable')
                                <span class="badge bg-primary">{{ __('messages.Variable') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.Simple') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block">
                                <input
                                    class="form-check-input toggle-active-btn"
                                    type="checkbox"
                                    id="toggleActive{{ $product->id }}"
                                    data-toggle-url="{{ route('v1.admin.products.toggle-active', $product) }}"
                                    @checked($product->is_active)
                                >
                                <label class="form-check-label" for="toggleActive{{ $product->id }}">
                                    @if ($product->is_active)
                                        <span class="badge bg-success">{{ __('messages.Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('messages.Inactive') }}</span>
                                    @endif
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block">
                                <input
                                    class="form-check-input toggle-featured-btn"
                                    type="checkbox"
                                    id="toggleFeatured{{ $product->id }}"
                                    data-toggle-url="{{ route('v1.admin.products.toggle-featured', $product) }}"
                                    @checked($product->is_featured)
                                >
                                <label class="form-check-label" for="toggleFeatured{{ $product->id }}">
                                    @if ($product->is_featured)
                                        <span class="badge bg-warning text-dark">{{ __('messages.Featured') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('messages.No') }}</span>
                                    @endif
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch d-inline-block">
                                <input
                                    class="form-check-input toggle-approved-btn"
                                    type="checkbox"
                                    id="toggleApproved{{ $product->id }}"
                                    data-toggle-url="{{ route('v1.admin.products.toggle-approved', $product) }}"
                                    @checked($product->is_approved)
                                >
                                <label class="form-check-label" for="toggleApproved{{ $product->id }}">
                                    @if ($product->is_approved)
                                        <span class="badge bg-success">{{ __('messages.Approved') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('messages.Pending') }}</span>
                                    @endif
                                </label>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">{{ $product->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm table-actions" role="group">
                                <a
                                    href="{{ route('v1.admin.products.show', $product) }}"
                                    class="btn btn-outline-info"
                                    title="{{ __('messages.View') }}"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a
                                    href="{{ route('v1.admin.products.edit', $product) }}"
                                    class="btn btn-outline-primary"
                                    title="{{ __('messages.Edit') }}"
                                >
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-outline-danger delete-product-btn"
                                    title="{{ __('messages.Delete') }}"
                                    data-product-name="{{ $nameLocale }}"
                                    data-delete-url="{{ route('v1.admin.products.destroy', $product) }}"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'box-seam',
        'message' => __('messages.No products.'),
        'createUrl' => route('v1.admin.products.create'),
        'createLabel' => __('messages.New product'),
    ])
@endif
