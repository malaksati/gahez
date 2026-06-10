@extends('layouts.app')

@php
    $page = 'variants';
    $locale = app()->getLocale();
    $name = $variant->getTranslation('name', $locale, false) ?: $variant->getTranslation('name', 'en');
    $badges = view('v1.admin.partials.active-badge', ['active' => $variant->is_active])->render();
    if ($variant->is_required) {
        $badges .= '<span class="badge bg-warning text-dark ms-1">'.__('messages.Required').'</span>';
    }
@endphp

@section('title', $name)
@section('heading', __('messages.Variant details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.variants.index',
        'indexLabel' => __('messages.Variants'),
        'title' => $name,
        'badges' => $badges,
        'editRoute' => route('v1.admin.variants.edit', $variant),
        'editLabel' => __('messages.Edit variant'),
        'destroyRoute' => route('v1.admin.variants.destroy', $variant),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Variant information') }}</h5>
                    <div class="row g-3">
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.English').')', 'value' => e($variant->getTranslation('name', 'en') ?: '—')])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.Arabic').')', 'value' => e($variant->getTranslation('name', 'ar') ?: '—'), 'dir' => 'rtl'])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Required'),
                            'value' => $variant->is_required ? __('messages.Yes') : __('messages.No'),
                        ])
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Variant options') }} ({{ $variant->options_count }})</h5>
                    @if ($variant->options->isEmpty())
                        <p class="text-muted mb-0">{{ __('messages.No options') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('messages.Name') }}</th>
                                        <th>{{ __('messages.Code') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($variant->options as $option)
                                        <tr>
                                            <td>{{ $option->getTranslation('name', $locale, false) ?: $option->getTranslation('name', 'en') ?: '—' }}</td>
                                            <td><code class="small">{{ $option->code ?: '—' }}</code></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @include('v1.admin.partials.show-timestamps', ['model' => $variant])
        </div>
    </div>
@endsection
