<?php

namespace App\Notifications;

use App\Models\ProductReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductReportSubmittedAdminNotification extends Notification
{
    use Queueable;

    public function __construct(public ProductReport $report) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->report->loadMissing('product', 'user');
        $locale = app()->getLocale();
        $productName = $this->report->product
            ? ($this->report->product->getTranslation('name', $locale, false)
                ?: $this->report->product->getTranslation('name', 'en', false))
            : null;

        return [
            'title' => __('messages.New product report'),
            'message' => __('messages.A customer reported :product.', [
                'product' => $productName ?: __('messages.Product').' #'.$this->report->product_id,
            ]),
            'url' => route('v1.admin.product-reports.index', ['status' => 'pending']),
            'product_report_id' => $this->report->id,
            'product_id' => $this->report->product_id,
        ];
    }
}
