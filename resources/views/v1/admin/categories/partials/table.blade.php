@php
    $locale = app()->getLocale();
    $sectioned = $sectioned ?? false;
@endphp

@if ($categories->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Image') }}</th>
                    <th>{{ __('messages.Name') }}</th>
                    <th>{{ __('messages.Sort order') }}</th>
                    <th>{{ __('messages.Products') }}</th>
                    @unless ($sectioned)
                        <th>{{ __('messages.Parent category') }}</th>
                    @endunless
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Featured') }}</th>
                    <th>{{ __('messages.Created') }}</th>
                    <th class="text-end">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    @php
                        $locale = app()->getLocale();
                        $displayName = $locale === 'ar'
                            ? ($category->getTranslation('name', 'ar', false) ?: $category->getTranslation('name', 'en'))
                            : ($category->getTranslation('name', 'en', false) ?: $category->getTranslation('name', 'ar'));
                        $childrenCount = $category->relationLoaded('children') ? $category->children->count() : 0;
                        $depth = (int) ($category->tree_depth ?? 0);
                        $childArrow = $depth > 0
                            ? '<span class="text-muted '.($locale === 'ar' ? 'ms-1' : 'me-1').'">'.($locale === 'ar' ? '↲' : '↳').'</span>'
                            : null;
                        $childrenBadge = $childrenCount > 0
                            ? '<span class="badge bg-info text-dark ms-2">'.$childrenCount.' '.__('messages.subcategories').'</span>'
                            : null;
                    @endphp
                    <tr data-category-row="{{ $category->id }}">
                        <td>
                            <img
                                src="{{ $category->image }}"
                                alt="{{ $displayName }}"
                                class="img-thumbnail"
                                style="width: 50px; height: 50px; object-fit: cover;"
                            >
                        </td>
                        <td>
                            <div style="padding-inline-start: {{ $depth * 1.25 }}rem;">
                                @include('v1.admin.partials.translatable-name-stack', [
                                    'model' => $category,
                                    'prefix' => $childArrow,
                                    'suffix' => $childrenBadge,
                                ])
                            </div>
                        </td>
                        <td class="text-muted">{{ $category->sort_order ?: '—' }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $category->products_count }}</span>
                        </td>
                        @unless ($sectioned)
                            <td>
                                @if ($category->parent)
                                    {{ $category->parent->getTranslation('name', $locale, false) ?: $category->parent->getTranslation('name', 'en') }}
                                @else
                                    <span class="text-muted">{{ __('messages.Root') }}</span>
                                @endif
                            </td>
                        @endunless
                        <td>
                            @if ($category->is_active)
                                <span class="badge bg-success">{{ __('messages.Active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($category->is_featured)
                                <span class="badge bg-primary">{{ __('messages.Featured') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $category->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm table-actions" role="group">
                                <a
                                    href="{{ route('v1.admin.categories.show', $category) }}"
                                    class="btn btn-outline-info"
                                    title="{{ __('messages.View') }}"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a
                                    href="{{ route('v1.admin.categories.edit', $category) }}"
                                    class="btn btn-outline-primary"
                                    title="{{ __('messages.Edit') }}"
                                >
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-outline-danger delete-category-btn"
                                    title="{{ __('messages.Delete') }}"
                                    data-category-name="{{ $displayName }}"
                                    data-delete-url="{{ route('v1.admin.categories.destroy', $category) }}"
                                    data-has-children="{{ $childrenCount > 0 ? 'true' : 'false' }}"
                                    data-children-count="{{ $childrenCount }}"
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
        'icon' => 'folder-x',
        'message' => __('messages.No categories in this section.'),
    ])
@endif
