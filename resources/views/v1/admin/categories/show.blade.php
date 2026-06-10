@extends('layouts.app')

@php
    $page = 'categories';
    $locale = app()->getLocale();
    $name = $category->getTranslation('name', $locale, false) ?: $category->getTranslation('name', 'en');
    $badges = '<span class="badge '.($category->is_active ? 'bg-success' : 'bg-secondary').'">'
        .($category->is_active ? __('messages.Active') : __('messages.Inactive')).'</span>';
    if ($category->is_featured) {
        $badges .= '<span class="badge bg-warning text-dark">'.__('messages.Featured').'</span>';
    }
@endphp

@section('title', $name)
@section('heading', __('messages.Category details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.categories.index',
        'indexLabel' => __('messages.Categories'),
        'title' => $name,
        'badges' => $badges,
        'editRoute' => route('v1.admin.categories.edit', $category),
        'editLabel' => __('messages.Edit category'),
        'destroyRoute' => route('v1.admin.categories.destroy', $category),
        'destroyConfirm' => __('messages.Delete this category?'),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            @if ($category->getRawOriginal('image'))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center">
                        <img src="{{ asset('storage/'.$category->getRawOriginal('image')) }}" alt="{{ $name }}"
                            class="img-fluid rounded" style="max-height: 220px; object-fit: cover;">
                    </div>
                </div>
            @endif
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Category information') }}</h5>
                    <div class="row g-3">
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.English').')', 'value' => e($category->getTranslation('name', 'en') ?: '—')])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.Arabic').')', 'value' => e($category->getTranslation('name', 'ar') ?: '—'), 'dir' => 'rtl'])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Slug'), 'value' => '<code>'.e($category->slug).'</code>'])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Sort order'),
                            'value' => $category->sort_order ? (string) $category->sort_order : '—',
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Parent category'),
                            'value' => $category->parent
                                ? '<a href="'.route('v1.admin.categories.show', $category->parent).'">'.e($category->parent->getTranslation('name', $locale, false) ?: $category->parent->getTranslation('name', 'en')).'</a>'
                                : e(__('messages.Root')),
                        ])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Subcategories'), 'value' => (string) $category->children_count])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Products'), 'value' => (string) $category->products_count])
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @include('v1.admin.partials.show-timestamps', ['model' => $category])
        </div>
    </div>
@endsection
