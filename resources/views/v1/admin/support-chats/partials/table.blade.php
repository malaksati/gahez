@php use Illuminate\Support\Str; @endphp
@if ($supports->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.ID') }}</th>
                    <th>{{ __('messages.Subject') }}</th>
                    <th>{{ __('messages.Customer') }}</th>
                    <th>{{ __('messages.Assigned agent') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Last activity') }}</th>
                    <th class="text-end" style="width: 100px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($supports as $support)
                    @php
                        $statusBadge = $support->status === 'open' ? 'success' : 'secondary';
                    @endphp
                    <tr>
                        <td><strong>#{{ $support->id }}</strong></td>
                        <td>{{ Str::limit($support->subject ?? __('messages.No subject'), 50) }}</td>
                        <td>{{ $support->user?->name ?? '—' }}</td>
                        <td>{{ $support->assignedAdmin?->name ?? __('messages.Unassigned') }}</td>
                        <td>
                            <span class="badge bg-{{ $statusBadge }} text-capitalize">
                                {{ __('messages.'.$support->status) }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            {{ $support->last_message_at?->format('M d, Y H:i') ?? '—' }}
                        </td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.support-chats.show', $support),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-3 pb-3">{{ $supports->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'chat-dots',
        'message' => __('messages.No support chats.'),
    ])
@endif
