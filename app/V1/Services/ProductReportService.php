<?php

namespace App\V1\Services;

use App\Models\ProductReport;
use App\Notifications\ProductReportSubmittedAdminNotification;
use App\V1\Repositories\ProductReportRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductReportService
{
    public function __construct(
        protected ProductReportRepository $reports,
        protected NotificationService $notifications,
    ) {}

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->reports->getPaginated($perPage, $filters);
    }

    public function reportProduct(int $userId, int $productId, ?string $reason = null, ?string $description = null): ProductReport
    {
        $report = ProductReport::query()->create([
            'product_id' => $productId,
            'user_id' => $userId,
            'reason' => $reason,
            'description' => $description,
            'status' => 'pending',
        ]);

        $this->notifications->notifyAdminsWithPermission(
            'manage product-reports',
            new ProductReportSubmittedAdminNotification($report),
        );

        return $report;
    }

    public function update(ProductReport $report, array $data): ProductReport
    {
        return $this->reports->update($report, $data);
    }
}
