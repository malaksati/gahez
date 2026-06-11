<div class="message-item mb-4 pb-4 border-bottom" data-message-id="{{ $message->id }}">
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
                <a href="{{ $attachmentUrl($attachment) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-paperclip me-1"></i>{{ basename($attachment) }}
                </a>
            @endforeach
        </div>
    @endif
</div>
