<?php

namespace App\V1\Http\Requests\Web\Admin\Reports;

use App\V1\Http\Requests\Web\AdminFormRequest;

class ReportDateRangeRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ];
    }
}
