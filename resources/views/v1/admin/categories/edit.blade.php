@extends('layouts.app')

@section('title', __('messages.Edit category'))
@section('heading', __('messages.Edit category'))

@section('content')

    <form action="{{ route('v1.admin.categories.update', $category) }}" method="POST" class="card card-body">
        @method('PUT')
        @include('v1.admin.categories._form')
    </form>
@endsection