@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $currency = display_currency();
@endphp

@section('title', $goal->getTranslation('name', $locale, false) ?: __('messages.Goal'))
@section('content')
    <div class="card card-body col-lg-8">
        <h5 class="mb-3">{{ $goal->getTranslation('name', $locale, false) ?: $goal->getTranslation('name', 'en') }}</h5>
        <p class="text-muted">{{ $goal->getTranslation('description', $locale, false) ?: '—' }}</p>

        <dl class="row mb-0">
            <dt class="col-sm-4">{{ __('messages.Period') }}</dt>
            <dd class="col-sm-8">{{ __('messages.Goal period '.$goal->period_type) }}</dd>

            <dt class="col-sm-4">{{ __('messages.Minimum order subtotal') }}</dt>
            <dd class="col-sm-8">{{ format_local_number((float) $goal->min_order_total, 2) }} {{ $currency }}</dd>

            <dt class="col-sm-4">{{ __('messages.Reward amount') }}</dt>
            <dd class="col-sm-8">{{ format_local_number((float) $goal->reward_amount, 2) }} {{ $currency }}</dd>

            <dt class="col-sm-4">{{ __('messages.Validity') }}</dt>
            <dd class="col-sm-8">@include('v1.admin.partials.validity-period', ['model' => $goal])</dd>

            <dt class="col-sm-4">{{ __('messages.Status') }}</dt>
            <dd class="col-sm-8">@include('v1.admin.partials.status-column', ['model' => $goal])</dd>
        </dl>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('v1.admin.goals.edit', $goal) }}" class="btn btn-primary">{{ __('messages.Edit') }}</a>
            <a href="{{ route('v1.admin.goals.index') }}" class="btn btn-outline-secondary">{{ __('messages.Back') }}</a>
        </div>
    </div>
@endsection
