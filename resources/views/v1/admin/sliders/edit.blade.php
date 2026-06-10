@extends('layouts.app')

@section('title', __('messages.Edit slider'))
@section('heading', __('messages.Edit slider'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <form action="{{ route('v1.admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data" class="card border-0 shadow-sm">
                @method('PUT')
                @include('v1.admin.sliders._form')
            </form>
        </div>
    </div>
@endsection