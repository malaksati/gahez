@extends('layouts.app')

@section('title', __('messages.New category'))
@section('heading', __('messages.New category'))

@section('content')

    <form action="{{ route('v1.admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="card card-body">
        @include('v1.admin.categories._form')
    </form>
@endsection