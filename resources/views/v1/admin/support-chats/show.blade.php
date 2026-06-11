@extends('layouts.app')

@php
    $page = 'support-chats';
    $attachmentUrl = static function (string $path): string {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    };
    $statusBadge = $support->status === 'open' ? 'success' : 'secondary';
    $badges = '<span class="badge bg-'.$statusBadge.' text-capitalize">'.e(__('messages.'.$support->status)).'</span>';
    $canReply = $support->isOpen();
    $title = $support->subject ?? __('messages.Support chat').' #'.$support->id;
    $websocketEnabled = config('broadcasting.default') === 'reverb';
@endphp

@section('title', $title)
@section('heading', __('messages.Support chat details'))

@push('head-config')
    <script>
        window.__supportChatRealtime = {
            supportId: @json($support->id),
            websocketEnabled: @json($websocketEnabled),
            pollUrl: @json(route('v1.admin.support-chats.messages.index', $support)),
            authEndpoint: @json(url('/admin/broadcasting/auth')),
            labels: {
                admin: @json(__('messages.Admin')),
                customer: @json(__('messages.Customer')),
                system: @json(__('messages.System')),
            },
        };
    </script>
@endpush

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.support-chats.index',
        'indexLabel' => __('messages.Support chats'),
        'title' => $title,
        'badges' => $badges,
    ])

    <div class="row g-4" data-support-chat-show data-support-id="{{ $support->id }}">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0" data-support-chat-count data-count-label="{{ __('messages.Messages') }}">
                        {{ __('messages.Messages') }} ({{ $messages->total() }})
                    </h2>
                </div>
                <div class="card-body">
                    @if ($messages->isNotEmpty())
                        <div class="messages-list" data-support-chat-messages style="max-height: 32rem; overflow-y: auto;">
                            @foreach ($messages as $message)
                                @include('v1.admin.support-chats.partials.message', [
                                    'message' => $message,
                                    'attachmentUrl' => $attachmentUrl,
                                ])
                            @endforeach
                        </div>
                        <div class="mt-3">{{ $messages->links() }}</div>
                    @else
                        <div class="text-center py-4 text-muted" data-support-chat-empty>
                            <i class="bi bi-chat-dots fs-3 d-block"></i>
                            <p class="mt-2 mb-0">{{ __('messages.No messages yet.') }}</p>
                        </div>
                        <div class="messages-list" data-support-chat-messages style="max-height: 32rem; overflow-y: auto;"></div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm {{ $canReply ? '' : 'ticket-messages-hidden' }}">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Add message') }}</h2>
                </div>
                <div class="card-body">
                    <form
                        action="{{ route('v1.admin.support-chats.messages.store', $support) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        data-support-chat-reply-form
                    >
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
                    <i class="bi bi-lock me-2"></i>{{ __('messages.Support chat is closed.') }}
                </div>
            @endunless
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Status') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('v1.admin.support-chats.status.update', $support) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            @foreach (['open', 'closed'] as $status)
                                <option value="{{ $status }}" @selected($support->status === $status)>
                                    {{ __('messages.'.$status) }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Assign agent') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('v1.admin.support-chats.assign', $support) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <select class="form-select @error('assigned_admin_id') is-invalid @enderror" name="assigned_admin_id" onchange="this.form.submit()">
                            <option value="">{{ __('messages.Select agent') }}</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}" @selected($support->assigned_admin_id === $admin->id)>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_admin_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Chat info') }}</h2>
                </div>
                <div class="card-body small">
                    <dl class="mb-0">
                        <dt class="text-muted">{{ __('messages.Chat ID') }}</dt>
                        <dd><code>#{{ $support->id }}</code></dd>

                        <dt class="text-muted mt-3">{{ __('messages.Customer') }}</dt>
                        <dd>{{ $support->user?->name ?? '—' }}</dd>

                        <dt class="text-muted mt-3">{{ __('messages.Subject') }}</dt>
                        <dd>{{ $support->subject ?? __('messages.No subject') }}</dd>

                        <dt class="text-muted mt-3">{{ __('messages.Last activity') }}</dt>
                        <dd>{{ $support->last_message_at?->format('M d, Y H:i') ?? '—' }}</dd>

                        <dt class="text-muted mt-3">{{ __('messages.Created at') }}</dt>
                        <dd>{{ $support->created_at?->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            @include('v1.admin.partials.show-timestamps', ['model' => $support])
        </div>
    </div>
@endsection
