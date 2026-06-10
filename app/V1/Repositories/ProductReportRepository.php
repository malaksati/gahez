<?php

namespace App\V1\Repositories;

use App\Models\ProductReport;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductReportRepository
{
    use AppliesInsensitiveSearch;

    public function __construct(
        protected ProductReport $model,
    ) {}

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model::query()->with(['product', 'user', 'handler']);

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];

            $query->where(function ($q) use ($search) {
                $this->applyColumnsSearchInsensitive($q, ['reason', 'description'], $search);
                $q->orWhereHas('product', fn ($productQuery) => $this->applyTranslatableNameSearch($productQuery, $search));
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function update(ProductReport $report, array $data): ProductReport
    {
        $report->update($data);

        return $report->fresh(['product', 'user', 'handler']);
    }
}
