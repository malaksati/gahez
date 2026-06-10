<?php

namespace App\V1\Repositories;

use App\Models\OrderRefundRequest;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRefundRequestRepository
{
    use AppliesInsensitiveSearch;

    public function __construct(
        protected OrderRefundRequest $model,
    ) {}

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['order.user', 'user', 'processor']);

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where(function ($q) use ($search) {
                $this->applyColumnsSearchInsensitive($q, ['reason', 'details'], $search);

                if (is_numeric($search)) {
                    $q->orWhere('order_id', (int) $search);
                }
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        match ((string) ($filters['sort'] ?? 'latest')) {
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    public function findById(int $id): OrderRefundRequest
    {
        return $this->model->newQuery()
            ->with(['order.user', 'user', 'processor'])
            ->findOrFail($id);
    }

    public function update(OrderRefundRequest $refundRequest, array $data): OrderRefundRequest
    {
        $refundRequest->update($data);

        return $refundRequest->fresh(['order.user', 'user', 'processor']);
    }
}
