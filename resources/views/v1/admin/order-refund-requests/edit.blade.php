@extends('layouts.app')

@section('title', __('messages.Edit refund request'))
@section('subtitle', __('messages.Refund request #:id', ['id' => $refundRequest->id]))

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Request details') }}</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">{{ __('messages.Order') }}</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('v1.admin.orders.edit', $refundRequest->order_id) }}">#{{ $refundRequest->order_id }}</a>
                        </dd>
                        <dt class="col-sm-4 text-muted">{{ __('messages.Customer') }}</dt>
                        <dd class="col-sm-8">{{ $refundRequest->user?->name ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">{{ __('messages.Reason') }}</dt>
                        <dd class="col-sm-8">{{ $refundRequest->reason ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">{{ __('messages.Details') }}</dt>
                        <dd class="col-sm-8">{{ $refundRequest->details ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">{{ __('messages.Date') }}</dt>
                        <dd class="col-sm-8">{{ $refundRequest->created_at?->format('M d, Y H:i') }}</dd>
                        @if ($refundRequest->processed_at)
                            <dt class="col-sm-4 text-muted">{{ __('messages.Processed at') }}</dt>
                            <dd class="col-sm-8">{{ $refundRequest->processed_at->format('M d, Y H:i') }}</dd>
                            <dt class="col-sm-4 text-muted">{{ __('messages.Processed by') }}</dt>
                            <dd class="col-sm-8">{{ $refundRequest->processor?->name ?? '—' }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <form action="{{ route('v1.admin.order-refund-requests.update', $refundRequest) }}" method="POST" class="card border-0 shadow-sm">
                @csrf
                @method('PUT')
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Update status') }}</h2>
                </div>
                <div class="card-body">
                    <label class="form-label">{{ __('messages.Status') }}</label>
                    <select name="status" class="form-select">
                        @foreach (['pending', 'approved', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $refundRequest->status) === $status)>
                                {{ __('messages.'.$status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
                    <a href="{{ route('v1.admin.order-refund-requests.index') }}" class="btn btn-outline-secondary">{{ __('messages.Back') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
