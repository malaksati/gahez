@extends('layouts.app')

@php
    $page = 'variant-options';
    $locale = app()->getLocale();
    $name = $variantOption->getTranslation('name', $locale, false) ?: $variantOption->getTranslation('name', 'en');
@endphp

@section('title', $name)
@section('heading', __('messages.Variant option details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.variant-options.index',
        'indexLabel' => __('messages.Variant options'),
        'title' => $name,
        'editRoute' => route('v1.admin.variant-options.edit', $variantOption),
        'editLabel' => __('messages.Edit option'),
        'destroyRoute' => route('v1.admin.variant-options.destroy', $variantOption),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Details') }}</h5>
                    <div class="row g-3">
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.English').')', 'value' => e($variantOption->getTranslation('name', 'en') ?: '—')])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.Arabic').')', 'value' => e($variantOption->getTranslation('name', 'ar') ?: '—'), 'dir' => 'rtl'])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Code'), 'value' => '<code>'.e($variantOption->code ?: '—').'</code>'])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Variant'),
                            'value' => $variantOption->variant
                                ? '<a href="'.route('v1.admin.variants.show', $variantOption->variant).'">'.e($variantOption->variant->getTranslation('name', $locale, false) ?: $variantOption->variant->getTranslation('name', 'en')).'</a>'
                                : '—',
                        ])
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @include('v1.admin.partials.show-timestamps', ['model' => $variantOption])
        </div>
    </div>
@endsection
