@extends('layouts.app')

@php $page = 'sliders'; @endphp

@section('title', __('messages.Slider').' #'.$slider->id)
@section('heading', __('messages.Slider details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.sliders.index',
        'indexLabel' => __('messages.Sliders'),
        'title' => __('messages.Slider').' #'.$slider->id,
        'editRoute' => route('v1.admin.sliders.edit', $slider),
        'editLabel' => __('messages.Edit slider'),
        'destroyRoute' => route('v1.admin.sliders.destroy', $slider),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">{{ __('messages.Image') }}</h5>
                    @if ($slider->image)
                        <img src="{{ asset('storage/'.$slider->image) }}" alt=""
                            class="img-fluid rounded border" style="max-height: 360px; object-fit: contain;">
                    @else
                        <p class="text-muted mb-0">—</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    @include('v1.admin.partials.show-field', [
                        'label' => __('messages.Slider type'),
                        'value' => \App\Models\Slider::typeLabel($slider->type),
                    ])
                </div>
            </div>
            @include('v1.admin.partials.show-timestamps', ['model' => $slider])
        </div>
    </div>
@endsection
