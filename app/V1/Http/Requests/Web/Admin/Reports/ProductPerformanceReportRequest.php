<?php

namespace App\V1\Http\Requests\Web\Admin\Reports;

class ProductPerformanceReportRequest extends ReportDateRangeRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,refunded'],
            'order_status' => ['nullable', 'in:pending,processing,shipped,delivered,cancelled,refunded'],
        ]);
    }
}
