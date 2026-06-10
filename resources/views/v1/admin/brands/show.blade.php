@extends('layouts.app')

@php
    $page = 'brands';
    $locale = app()->getLocale();
    $name = $brand->getTranslation('name', $locale, false) ?: $brand->getTranslation('name', 'en');
@endphp

@section('title', $name)
@section('heading', __('messages.Brand details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.brands.index',
        'indexLabel' => __('messages.Brands'),
        'title' => $name,
        'editRoute' => route('v1.admin.brands.edit', $brand),
        'editLabel' => __('messages.Edit brand'),
        'destroyRoute' => route('v1.admin.brands.destroy', $brand),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Brand information') }}</h5>
                    <div class="row g-3">
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.English').')', 'value' => e($brand->getTranslation('name', 'en') ?: '—')])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.Arabic').')', 'value' => e($brand->getTranslation('name', 'ar') ?: '—'), 'dir' => 'rtl'])
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @include('v1.admin.partials.show-timestamps', ['model' => $brand])
        </div>
    </div>
@endsection
