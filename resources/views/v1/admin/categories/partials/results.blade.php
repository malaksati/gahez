@php
    $locale = app()->getLocale();
@endphp

<div class="accordion mb-4" id="categoriesAccordion">
    @foreach ($categorySections as $section)
        @php
            $root = $section['root'];
            $tree = $section['tree'];
            $rootName = $root->getTranslation('name', $locale, false) ?: $root->getTranslation('name', 'en');
            $descendantCount = max(0, $tree->count() - 1);
        @endphp
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingCategory{{ $root->id }}">
                <button
                    class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseCategory{{ $root->id }}"
                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                    aria-controls="collapseCategory{{ $root->id }}"
                >
                    <strong>{{ $rootName }}</strong>
                    @if ($descendantCount > 0)
                        <span class="badge bg-secondary ms-2">
                            {{ $descendantCount }} {{ __('messages.subcategories') }}
                        </span>
                    @endif
                </button>
            </h2>
            <div
                id="collapseCategory{{ $root->id }}"
                class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                aria-labelledby="headingCategory{{ $root->id }}"
                data-bs-parent="#categoriesAccordion"
            >
                <div class="accordion-body p-0">
                    @include('v1.admin.categories.partials.table', [
                        'categories' => $tree,
                        'sectioned' => true,
                    ])
                </div>
            </div>
        </div>
    @endforeach

    @if ($categorySections->isEmpty() && $orphanCategories->isEmpty())
        @include('v1.admin.partials.table-empty', [
            'icon' => 'folder-x',
            'message' => __('messages.No categories yet.'),
            'createUrl' => route('v1.admin.categories.create'),
            'createLabel' => __('messages.New category'),
        ])
    @endif
</div>

@if ($rootCategories->hasPages())
    <div class="mt-4">
        {{ $rootCategories->withQueryString()->links() }}
    </div>
@endif

@if ($orphanCategories->isNotEmpty())
    <div class="accordion mb-4" id="orphanCategoriesAccordion">
        <div class="accordion-item border-warning">
            <h2 class="accordion-header" id="headingOrphanCategories">
                <button
                    class="accordion-button collapsed bg-warning bg-opacity-10"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseOrphanCategories"
                    aria-expanded="false"
                    aria-controls="collapseOrphanCategories"
                >
                    <strong>{{ __('messages.Unassigned categories') }}</strong>
                    <span class="badge bg-secondary ms-2">
                        {{ $orphanCategories->count() }} {{ __('messages.Categories') }}
                    </span>
                </button>
            </h2>
            <div
                id="collapseOrphanCategories"
                class="accordion-collapse collapse"
                aria-labelledby="headingOrphanCategories"
            >
                <div class="accordion-body p-0">
                    @include('v1.admin.categories.partials.table', [
                        'categories' => $orphanCategories,
                        'sectioned' => false,
                    ])
                </div>
            </div>
        </div>
    </div>
@endif
