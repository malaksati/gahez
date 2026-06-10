@php
    $latestLog = $order->logs->first();
@endphp
@if ($latestLog)
    <div class="small">
        <div>{{ $latestLog->label() }}</div>
        <div class="text-muted">{{ $latestLog->created_at?->format('M d, Y H:i') }}</div>
        @if ($order->logs_count > 1)
            <span class="badge bg-light text-dark border mt-1">{{ $order->logs_count }} {{ __('messages.log entries') }}</span>
        @endif
    </div>
@else
    <span class="text-muted small">—</span>
@endif
