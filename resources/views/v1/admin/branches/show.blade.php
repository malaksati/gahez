@extends('layouts.app')

@php
    $page = 'branches';
    $locale = app()->getLocale();
    $name = $branch->getTranslation('name', $locale, false) ?: $branch->getTranslation('name', 'en');
    $badges = view('v1.admin.partials.active-badge', ['active' => $branch->is_active])->render();
@endphp

@section('title', $name)
@section('heading', __('messages.Branch details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.branches.index',
        'indexLabel' => __('messages.Branches'),
        'title' => $name,
        'badges' => $badges,
        'editRoute' => route('v1.admin.branches.edit', $branch),
        'editLabel' => __('messages.Edit branch'),
        'destroyRoute' => route('v1.admin.branches.destroy', $branch),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Branch information') }}</h5>
                    <div class="row g-3">
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.English').')', 'value' => e($branch->getTranslation('name', 'en') ?: '—')])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.Arabic').')', 'value' => e($branch->getTranslation('name', 'ar') ?: '—'), 'dir' => 'rtl'])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Address'), 'value' => e($branch->address ?: '—'), 'col' => 'col-12'])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Phone'), 'value' => e($branch->phone ?: '—')])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Latitude'), 'value' => $branch->latitude !== null ? e((string) $branch->latitude) : '—'])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Longitude'), 'value' => $branch->longitude !== null ? e((string) $branch->longitude) : '—'])
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @include('v1.admin.partials.show-timestamps', ['model' => $branch])
        </div>
    </div>
@endsection
