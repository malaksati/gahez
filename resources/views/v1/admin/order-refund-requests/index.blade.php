@extends('layouts.app')

@section('title', __('messages.Order refund requests'))
@section('subtitle', __('messages.Manage order refund requests'))

@section('content')
    @include('v1.admin.order-refund-requests.partials.filters')
    <div data-admin-list-results>
        @include('v1.admin.order-refund-requests.partials.results', ['refundRequests' => $refundRequests])
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', function(event) {
    const approveBtn = event.target.closest('.refund-request-approve-btn');
    if (approveBtn) {
        event.preventDefault();
        const form = approveBtn.closest('form');
        if (!form) {
            return;
        }

        const submit = function() {
            form.submit();
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: @json(__('messages.Approve this refund request?')),
                text: @json(__('messages.The order will be refunded to the customer.')),
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: @json(__('messages.Yes, accept')),
                cancelButtonText: @json(__('messages.Cancel')),
            }).then(function(result) {
                if (result.isConfirmed) {
                    submit();
                }
            });
        } else if (window.confirm(@json(__('messages.Approve this refund request?')))) {
            submit();
        }

        return;
    }

    const rejectBtn = event.target.closest('.refund-request-reject-btn');
    if (!rejectBtn) {
        return;
    }

    event.preventDefault();
    const form = rejectBtn.closest('form');
    if (!form) {
        return;
    }

    const submit = function() {
        form.submit();
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: @json(__('messages.Reject this refund request?')),
            text: @json(__('messages.The customer will be notified that the refund was rejected.')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: @json(__('messages.Reject')),
            cancelButtonText: @json(__('messages.Cancel')),
            confirmButtonColor: '#dc3545',
        }).then(function(result) {
            if (result.isConfirmed) {
                submit();
            }
        });
    } else if (window.confirm(@json(__('messages.Reject this refund request?')))) {
        submit();
    }
});
</script>
@endpush
