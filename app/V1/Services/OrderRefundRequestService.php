<?php

namespace App\V1\Services;

use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Notifications\OrderRefundRequestSubmittedAdminNotification;
use App\V1\Repositories\OrderRefundRequestRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderRefundRequestService
{
    public function __construct(
        protected OrderRefundRequestRepository $refundRequests,
        protected OrderService $orders,
        protected NotificationService $notifications,
    ) {}

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->refundRequests->getPaginated($perPage, $filters);
    }

    public function getPaginatedForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return OrderRefundRequest::query()
            ->where('user_id', $userId)
            ->with(['order'])
            ->latest()
            ->paginate($perPage);
    }

    public function getById(int $id): OrderRefundRequest
    {
        return $this->refundRequests->findById($id);
    }

    public function approve(OrderRefundRequest $refundRequest): OrderRefundRequest
    {
        return $this->update($refundRequest, ['status' => 'approved']);
    }

    public function reject(OrderRefundRequest $refundRequest): OrderRefundRequest
    {
        return $this->update($refundRequest, ['status' => 'rejected']);
    }

    public function update(OrderRefundRequest $refundRequest, array $data): OrderRefundRequest
    {
        return DB::transaction(function () use ($refundRequest, $data) {
            $refundRequest->refresh();
            $previousStatus = $refundRequest->status;
            $newStatus = $data['status'] ?? $previousStatus;

            if ($newStatus === 'approved' && $previousStatus !== 'approved') {
                if ($previousStatus !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => ['Only pending refund requests can be approved.'],
                    ]);
                }

                $order = $refundRequest->order;

                if (! $order) {
                    throw ValidationException::withMessages([
                        'order' => ['Order not found for this refund request.'],
                    ]);
                }

                if ($order->refund_status === 'refunded') {
                    throw ValidationException::withMessages([
                        'order' => ['This order has already been refunded.'],
                    ]);
                }

                // wallet refund
                $this->orders->refundOrder($order, (int) $refundRequest->user_id);

                $data['processed_by'] = Auth::id();
                $data['processed_at'] = now();
            }

            if ($newStatus === 'rejected' && $previousStatus === 'pending') {
                $data['processed_by'] = Auth::id();
                $data['processed_at'] = now();
            }

            $updated = $this->refundRequests->update($refundRequest, $data);

            if ($newStatus === 'rejected') {
                $this->syncOrderRefundStatus($updated);
            }

            return $updated;
        });
    }

    public function createForUser(Order $order, int $userId, ?string $reason = null, ?string $details = null): OrderRefundRequest
    {
        if ($order->user_id !== $userId) {
            throw ValidationException::withMessages([
                'order' => ['Order not found.'],
            ]);
        }

        if ($order->refund_status === 'refunded') {
            throw ValidationException::withMessages([
                'order' => ['This order has already been refunded.'],
            ]);
        }

        if (! in_array($order->status, ['delivered', 'shipped'], true)) {
            throw ValidationException::withMessages([
                'order' => ['Refund can only be requested for shipped or delivered orders.'],
            ]);
        }

        $pendingExists = OrderRefundRequest::query()
            ->where('order_id', $order->id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->exists();

        if ($pendingExists) {
            throw ValidationException::withMessages([
                'order' => ['A refund request is already pending for this order.'],
            ]);
        }

        $refundRequest = OrderRefundRequest::query()->create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'status' => 'pending',
            'reason' => $reason,
            'details' => $details,
        ]);

        $this->notifications->notifyAdminsWithPermission(
            'manage refunds',
            new OrderRefundRequestSubmittedAdminNotification($refundRequest),
        );

        return $refundRequest;
    }

    protected function syncOrderRefundStatus(OrderRefundRequest $refundRequest): void
    {
        $order = $refundRequest->order;

        if (! $order || $refundRequest->status !== 'rejected') {
            return;
        }

        if ($order->refund_status !== 'refunded') {
            $order->update(['refund_status' => 'rejected']);
        }
    }
}
