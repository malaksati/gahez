@extends('layouts.app')

@php
    $page = 'tickets';
    $ticketAttachmentUrl = static function (string $path): string {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    };
    $statusBadge = match ($ticket->status) {
        'pending' => 'warning',
        'resolved' => 'success',
        default => 'secondary',
    };
    $badges = '<span class="badge bg-'.$statusBadge.' text-capitalize">'.e(__('messages.'.$ticket->status)).'</span>';
    $canReply = $ticket->status !== 'closed';
@endphp

@section('title', $ticket->subject)
@section('heading', __('messages.Ticket details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.tickets.index',
        'indexLabel' => __('messages.Tickets'),
        'title' => $ticket->subject,
        'badges' => $badges,
        'editRoute' => route('v1.admin.tickets.edit', $ticket),
        'editLabel' => __('messages.Edit ticket'),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Ticket information') }}</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-3 text-muted">{{ __('messages.Customer') }}</dt>
                        <dd class="col-sm-9">{{ $ticket->user?->name ?? '—' }}</dd>

                        <dt class="col-sm-3 text-muted mt-3">{{ __('messages.Subject') }}</dt>
                        <dd class="col-sm-9 mt-3"><strong>{{ $ticket->subject }}</strong></dd>

                        <dt class="col-sm-3 text-muted mt-3">{{ __('messages.Description') }}</dt>
                        <dd class="col-sm-9 mt-3">
                            <div class="bg-light rounded p-3 mb-0">{{ $ticket->description }}</div>
                        </dd>

                        @if ($ticket->attachments && count($ticket->attachments) > 0)
                            <dt class="col-sm-3 text-muted mt-3">{{ __('messages.Attachments') }}</dt>
                            <dd class="col-sm-9 mt-3">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($ticket->attachments as $attachment)
                                        <a href="{{ $ticketAttachmentUrl($attachment) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-paperclip me-1"></i>{{ basename($attachment) }}
                                        </a>
                                    @endforeach
                                </div>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0">{{ __('messages.Messages') }} ({{ $ticket->messages->count() }})</h2>
                </div>
                <div class="card-body">
                    @if ($ticket->messages->isNotEmpty())
                        <div class="messages-list">
                            @foreach ($ticket->messages as $message)
                                <div class="message-item mb-4 pb-4 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong>{{ $message->sender?->name ?? __('messages.System') }}</strong>
                                            @if ($message->sender_type === 'admin')
                                                <span class="badge bg-danger ms-2">{{ __('messages.Admin') }}</span>
                                            @else
                                                <span class="badge bg-info ms-2">{{ __('messages.Customer') }}</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $message->created_at?->format('M d, Y H:i') }}</small>
                                    </div>
                                    <div class="bg-light rounded p-3 mb-2">{{ $message->message }}</div>
                                    @if ($message->attachments && count($message->attachments) > 0)
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($message->attachments as $attachment)
                                                <a href="{{ $ticketAttachmentUrl($attachment) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-paperclip me-1"></i>{{ basename($attachment) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat-dots fs-3 d-block"></i>
                            <p class="mt-2 mb-0">{{ __('messages.No messages yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm {{ $canReply ? '' : 'ticket-messages-hidden' }}">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Add message') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('v1.admin.tickets.messages.store', $ticket) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="message" class="form-label">{{ __('messages.Message') }} *</label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message"
                                      name="message"
                                      rows="4"
                                      placeholder="{{ __('messages.Type your message...') }}"
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="attachments" class="form-label">{{ __('messages.Attachments') }}</label>
                            <input type="file"
                                   class="form-control @error('attachments.*') is-invalid @enderror"
                                   id="attachments"
                                   name="attachments[]"
                                   accept="image/*,.pdf,.doc,.docx"
                                   multiple>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('messages.Optional attach files to your message') }}</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>{{ __('messages.Send message') }}
                        </button>
                    </form>
                </div>
            </div>

            @unless ($canReply)
                <div class="alert alert-secondary mt-3 mb-0">
                    <i class="bi bi-lock me-2"></i>{{ __('messages.This ticket is closed. Change the status to reply.') }}
                </div>
            @endunless
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Status') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('v1.admin.tickets.status.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            @foreach (['pending', 'resolved', 'closed'] as $status)
                                <option value="{{ $status }}" @selected($ticket->status === $status)>
                                    {{ __('messages.'.$status) }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Ticket info') }}</h2>
                </div>
                <div class="card-body small">
                    <dl class="mb-0">
                        <dt class="text-muted">{{ __('messages.Ticket ID') }}</dt>
                        <dd><code>#{{ $ticket->id }}</code></dd>

                        <dt class="text-muted mt-3">{{ __('messages.Messages') }}</dt>
                        <dd><span class="badge bg-secondary">{{ $ticket->messages->count() }}</span></dd>

                        <dt class="text-muted mt-3">{{ __('messages.Created at') }}</dt>
                        <dd>{{ $ticket->created_at?->format('M d, Y H:i') }}</dd>

                        <dt class="text-muted mt-3">{{ __('messages.Updated at') }}</dt>
                        <dd>{{ $ticket->updated_at?->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            @include('v1.admin.partials.show-timestamps', ['model' => $ticket])
        </div>
    </div>
@endsection
