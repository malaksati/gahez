@extends('layouts.app')

@section('title', __('messages.New slider'))
@section('heading', __('messages.New slider'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <form action="{{ route('v1.admin.sliders.store') }}" method="POST" enctype="multipart/form-data" class="card border-0 shadow-sm">
                @include('v1.admin.sliders._form')
            </form>
        </div>
    </div>
@endsection