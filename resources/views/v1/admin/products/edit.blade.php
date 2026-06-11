@extends('layouts.app')

@php
    $page = 'products';
@endphp

@section('title', __('messages.Edit product'))
@section('heading', __('messages.Edit product'))

@section('content')
    @include('v1.admin.products._wizard', [
        'product' => $product,
        'formAction' => route('v1.admin.products.update', $product),
        'brands' => $brands,
        'categories' => $categories,
        'allProducts' => $allProducts,
        'catalogVariants' => $catalogVariants,
        'catalogUnits' => $catalogUnits,
        'existingProductVariants' => $existingProductVariants,
        'existingProductUnits' => $existingProductUnits,
    ])
@endsection
