@php
    $locale = app()->getLocale();
    $showAllProducts = $showAllProducts ?? false;
    $uncategorizedCount = $uncategorizedCount ?? 0;
    $uncategorizedProducts = $uncategorizedProducts ?? collect();
@endphp

@if ($showAllProducts)
    <div class="card border-0 shadow-sm mb-4" data-product-list-search>
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0">
                {{ __('messages.All products') }}
                <span class="badge bg-secondary ms-1">{{ $allProducts->total() }} {{ __('messages.Products') }}</span>
            </h5>
            @if ($uncategorizedCount > 0)
                <span class="badge bg-warning text-dark">
                    {{ __('messages.Uncategorized') }}: {{ $uncategorizedCount }}
                </span>
            @endif
        </div>
        <div class="card-body p-0">
            @if ($allProducts->count() > 0)
                <div class="p-3 border-bottom bg-light">
                    <input
                        type="search"
                        class="form-control form-control-sm"
                        data-category-product-search
                        placeholder="{{ __('messages.Search products') }}"
                        autocomplete="off"
                    >
                </div>
                @include('v1.admin.products.partials.table', [
                    'products' => $allProducts,
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
    </div>

    @if ($allProducts->hasPages())
        <div class="mt-3">
            {{ $allProducts->withQueryString()->links() }}
        </div>
    @endif
@else
    <div class="accordion mb-4" id="categoriesAccordion">
        @foreach ($categories as $category)
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingCategory{{ $category->id }}">
                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategory{{ $category->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapseCategory{{ $category->id }}">
                        <strong>{{ $category->getTranslation('name', $locale, false) ?: $category->getTranslation('name', 'en') }}</strong>
                        <span class="badge bg-secondary ms-2">{{ $category->products->count() }} {{ __('messages.Products') }}</span>
                    </button>
                </h2>
                <div id="collapseCategory{{ $category->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="headingCategory{{ $category->id }}" data-bs-parent="#categoriesAccordion">
                    <div class="accordion-body p-0">
                        <div class="p-3 border-bottom bg-light">
                            <input
                                type="search"
                                class="form-control form-control-sm"
                                data-category-product-search
                                placeholder="{{ __('messages.Search products in this category') }}"
                                autocomplete="off"
                            >
                        </div>
                        @include('v1.admin.products.partials.table', ['products' => $category->products])
                    </div>
                </div>
            </div>
        @endforeach

        @if ($categories->count() == 0 && $uncategorizedCount == 0)
            @include('v1.admin.partials.table-empty', [
                'icon' => 'box-seam',
                'message' => __('messages.No products.'),
                'createUrl' => route('v1.admin.products.create'),
                'createLabel' => __('messages.New product'),
            ])
        @endif
    </div>

    @if ($categories->hasPages())
        <div class="mt-4">
            {{ $categories->withQueryString()->links() }}
        </div>
    @endif

    @if ($uncategorizedProducts->isNotEmpty())
        <div class="accordion mb-4" id="uncategorizedAccordion">
            <div class="accordion-item border-warning">
                <h2 class="accordion-header" id="headingUncategorized">
                    <button class="accordion-button collapsed bg-warning bg-opacity-10" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUncategorized" aria-expanded="false" aria-controls="collapseUncategorized">
                        <strong>{{ __('messages.Uncategorized') }}</strong>
                        <span class="badge bg-secondary ms-2">{{ $uncategorizedProducts->count() }} {{ __('messages.Products') }}</span>
                    </button>
                </h2>
                <div id="collapseUncategorized" class="accordion-collapse collapse" aria-labelledby="headingUncategorized" data-bs-parent="#uncategorizedAccordion">
                    <div class="accordion-body p-0">
                        <div class="p-3 border-bottom bg-light">
                            <input
                                type="search"
                                class="form-control form-control-sm"
                                data-category-product-search
                                placeholder="{{ __('messages.Search products in this category') }}"
                                autocomplete="off"
                            >
                        </div>
                        @include('v1.admin.products.partials.table', ['products' => $uncategorizedProducts])
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
