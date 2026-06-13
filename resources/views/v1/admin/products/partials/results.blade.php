@php
    $uncategorizedCount = $uncategorizedCount ?? 0;
@endphp

<div class="card border-0 shadow-sm mb-4" data-product-list-search>
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0">
            {{ __('messages.All products') }}
            <span class="badge bg-secondary ms-1">@num($products->total()) {{ __('messages.Products') }}</span>
        </h5>
        @if ($uncategorizedCount > 0)
            <span class="badge bg-warning text-dark">
                {{ __('messages.Uncategorized') }}: {{ $uncategorizedCount }}
            </span>
        @endif
    </div>
    <div class="card-body p-0">
        @if ($products->count() > 0)
            {{-- <div class="p-3 border-bottom product-list-search-bar">
                <input
                    type="search"
                    class="form-control form-control-sm"
                    data-category-product-search
                    placeholder="{{ __('messages.Search products') }}"
                    autocomplete="off"
                >
            </div> --}}
            @include('v1.admin.products.partials.table', [
                'products' => $products,
                'showCategoriesColumn' => true,
            ])
        @else
            @include('v1.admin.partials.table-empty', [
                'icon' => 'box-seam',
                'message' => __('messages.No products.'),
                'createUrl' => route('v1.admin.products.create'),
                'createLabel' => __('messages.New product'),
            ])
        @endif
    </div>
    @if ($products->total() > 0)
        <div class="card-footer bg-white border-top py-3 px-3">
            {{ $products->onEachSide(1)->withQueryString()->links() }}
        </div>
    @endif
</div>
