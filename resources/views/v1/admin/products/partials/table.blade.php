@php
    $locale = app()->getLocale();
    $currency = display_currency();
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
                    <th class="product-table-toggle-col">{{ __('messages.Status') }}</th>
                    <th class="product-table-toggle-col">{{ __('messages.Featured') }}</th>
                    <th class="product-table-toggle-col">{{ __('messages.Approved') }}</th>
                    <th>{{ __('messages.Created') }}</th>
                    <th class="text-end">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    @php
                        $displayName = $locale === 'ar'
                            ? ($product->getTranslation('name', 'ar', false) ?: $product->getTranslation('name', 'en'))
                            : ($product->getTranslation('name', 'en', false) ?: $product->getTranslation('name', 'ar'));
                        $brandName = $product->displayBrandName($locale) ?? '';
                        $searchText = mb_strtolower(implode(' ', array_filter([
                            $displayName,
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
                                alt="{{ $displayName }}"
                                class="img-thumbnail"
                                style="width: 50px; height: 50px; object-fit: cover;"
                            >
                        </td>
                        <td>
                            @include('v1.admin.partials.translatable-name-stack', ['model' => $product])
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
                            <strong>{{ format_local_number((float) $product->price, 2) }}{{ $currency ? ' '.$currency : '' }}</strong>
                            @if ($product->hasDiscount())
                                <br>
                                <small class="text-success">
                                    {{ format_local_number($product->final_price, 2) }}{{ $currency ? ' '.$currency : '' }}
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
                        <td class="product-table-toggle-col">
                            <div class="product-table-toggle-cell">
                                <div class="form-check form-switch product-table-toggle-switch">
                                    <input
                                        class="form-check-input toggle-active-btn"
                                        type="checkbox"
                                        id="toggleActive{{ $product->id }}"
                                        data-toggle-url="{{ route('v1.admin.products.toggle-active', $product) }}"
                                        aria-label="{{ __('messages.Status') }}"
                                        @checked($product->is_active)
                                    >
                                </div>
                                @if ($product->is_active)
                                    <span class="badge bg-success">{{ __('messages.Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.Inactive') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="product-table-toggle-col">
                            <div class="product-table-toggle-cell">
                                <div class="form-check form-switch product-table-toggle-switch">
                                    <input
                                        class="form-check-input toggle-featured-btn"
                                        type="checkbox"
                                        id="toggleFeatured{{ $product->id }}"
                                        data-toggle-url="{{ route('v1.admin.products.toggle-featured', $product) }}"
                                        aria-label="{{ __('messages.Featured') }}"
                                        @checked($product->is_featured)
                                    >
                                </div>
                                @if ($product->is_featured)
                                    <span class="badge bg-warning text-dark">{{ __('messages.Featured') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.No') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="product-table-toggle-col">
                            <div class="product-table-toggle-cell">
                                <div class="form-check form-switch product-table-toggle-switch">
                                    <input
                                        class="form-check-input toggle-approved-btn"
                                        type="checkbox"
                                        id="toggleApproved{{ $product->id }}"
                                        data-toggle-url="{{ route('v1.admin.products.toggle-approved', $product) }}"
                                        aria-label="{{ __('messages.Approved') }}"
                                        @checked($product->is_approved)
                                    >
                                </div>
                                @if ($product->is_approved)
                                    <span class="badge bg-success">{{ __('messages.Approved') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('messages.Pending') }}</span>
                                @endif
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
                                    data-product-name="{{ $displayName }}"
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
