@extends('layouts.app')

@php
    $page = 'products';
@endphp

@section('title', __('messages.New product'))
@section('heading', __('messages.New product'))

@section('content')
    @include('v1.admin.products._wizard', [
        'formAction' => route('v1.admin.products.store'),
        'brands' => $brands,
        'categories' => $categories,
        'allProducts' => $allProducts,
        'catalogVariants' => $catalogVariants,
        'existingProductVariants' => $existingProductVariants,
    ])
@endsection
