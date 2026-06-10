@extends('layouts.app')

@php
    $page = 'variants';
@endphp

@section('title', __('messages.New variant'))
@section('heading', __('messages.New variant'))

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <p class="text-muted mb-4">{{ __('messages.Add variant with options hint') }}</p>
            <form action="{{ route('v1.admin.variants.store') }}" method="POST" id="variantForm">
                @include('v1.admin.variants._form')
            </form>
        </div>
    </div>
@endsection
