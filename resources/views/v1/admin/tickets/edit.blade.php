@extends('layouts.app')

@php
    $page = 'tickets';
    $statusBadge = match ($ticket->status) {
        'pending' => 'warning',
        'resolved' => 'success',
        default => 'secondary',
    };
    $badges = '<span class="badge bg-'.$statusBadge.' text-capitalize">'.e(__('messages.'.$ticket->status)).'</span>';
@endphp

@section('title', __('messages.Edit ticket'))
@section('heading', __('messages.Ticket #:id', ['id' => $ticket->id]))

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('v1.admin.dashboard') }}">{{ __('messages.Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('v1.admin.tickets.index') }}">{{ __('messages.Tickets') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('v1.admin.tickets.show', $ticket) }}">{{ Str::limit($ticket->subject, 40) }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('messages.Edit') }}</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h2 class="h4 mb-2">{{ __('messages.Edit ticket') }}</h2>
                <div class="d-flex flex-wrap gap-2">{!! $badges !!}</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('v1.admin.tickets.show', $ticket) }}" class="btn btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>{{ __('messages.View ticket') }}
                </a>
                <a href="{{ route('v1.admin.tickets.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h2 class="h6 mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>{{ __('messages.Ticket information') }}
                    </h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">{{ __('messages.Ticket ID') }}</dt>
                        <dd class="col-sm-8"><code>#{{ $ticket->id }}</code></dd>

                        <dt class="col-sm-4 text-muted mt-3">{{ __('messages.Customer') }}</dt>
                        <dd class="col-sm-8">
                            @if ($ticket->user)
                                <strong>{{ $ticket->user->name }}</strong>
                                @if ($ticket->user->email)
                                    <br><span class="text-muted">{{ $ticket->user->email }}</span>
                                @endif
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted mt-3">{{ __('messages.Messages') }}</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary">{{ $ticket->messages->count() }}</span>
                        </dd>

                        <dt class="col-sm-4 text-muted mt-3">{{ __('messages.Created at') }}</dt>
                        <dd class="col-sm-8">{{ $ticket->created_at?->format('M d, Y H:i') ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted mt-3">{{ __('messages.Updated at') }}</dt>
                        <dd class="col-sm-8">{{ $ticket->updated_at?->format('M d, Y H:i') ?? '—' }}</dd>

                        @if ($ticket->attachments && count($ticket->attachments) > 0)
                            <dt class="col-sm-4 text-muted mt-3">{{ __('messages.Attachments') }}</dt>
                            <dd class="col-sm-8 mt-3">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($ticket->attachments as $attachment)
                                        <a href="{{ asset('storage/'.$attachment) }}" target="_blank" rel="noopener"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-paperclip me-1"></i>{{ basename($attachment) }}
                                        </a>
                                    @endforeach
                                </div>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <form action="{{ route('v1.admin.tickets.update', $ticket) }}" method="POST"
                class="card border-0 shadow-sm h-100">
                @csrf
                @method('PUT')

                <div class="card-header bg-white border-bottom">
                    <h2 class="h6 mb-0">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>{{ __('messages.Update ticket') }}
                    </h2>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label for="subject" class="form-label">{{ __('messages.Subject') }}</label>
                        <input type="text"
                            id="subject"
                            name="subject"
                            value="{{ old('subject', $ticket->subject) }}"
                            class="form-control @error('subject') is-invalid @enderror"
                            maxlength="255"
                            required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('messages.Description') }}</label>
                        <textarea id="description"
                            name="description"
                            rows="6"
                            class="form-control @error('description') is-invalid @enderror"
                            required>{{ old('description', $ticket->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="status" class="form-label">{{ __('messages.Status') }}</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach (['pending', 'resolved', 'closed'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $ticket->status) === $status)>
                                    {{ __('messages.'.$status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>{{ __('messages.This ticket is closed. Change the status to reply.') }}
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>{{ __('messages.Save changes') }}
                    </button>
                    <a href="{{ route('v1.admin.tickets.show', $ticket) }}" class="btn btn-outline-secondary">
                        {{ __('messages.Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
