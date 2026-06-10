@extends('layouts.app')

@php
    $page = 'variants';
@endphp

@section('title', __('messages.Edit variant'))
@section('heading', __('messages.Edit variant'))

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <p class="text-muted mb-4">{{ __('messages.Update variant with options hint') }}</p>
            <form action="{{ route('v1.admin.variants.update', $variant) }}" method="POST" id="variantForm">
                @method('PUT')
                @include('v1.admin.variants._form', ['variant' => $variant])
            </form>
        </div>
    </div>
@endsection
