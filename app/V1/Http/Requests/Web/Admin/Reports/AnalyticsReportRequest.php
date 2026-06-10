<?php

namespace App\V1\Http\Requests\Web\Admin\Reports;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'period_type' => ['nullable', Rule::in(['daily', 'weekly', 'monthly', 'manual'])],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $periodType = (string) $this->input('period_type', 'monthly');

        return [
            'from_date' => $periodType === 'manual' ? $this->input('from_date') : null,
            'to_date' => $periodType === 'manual' ? $this->input('to_date') : null,
            'period_type' => $periodType,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvedFilters(): array
    {
        $filters = $this->filters();
        [$from, $to] = $this->resolveDateRange($filters);

        $filters['resolved_from'] = $from->toDateString();
        $filters['resolved_to'] = $to->toDateString();

        return $filters;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function resolveDateRange(array $filters): array
    {
        $periodType = (string) ($filters['period_type'] ?? 'monthly');
        $today = CarbonImmutable::today();

        return match ($periodType) {
            'daily' => [$today->startOfDay(), $today->endOfDay()],
            'weekly' => [$today->subDays(6)->startOfDay(), $today->endOfDay()],
            'monthly' => [$today->subMonth()->startOfDay(), $today->endOfDay()],
            default => [
                ! empty($filters['from_date'])
                    ? CarbonImmutable::parse($filters['from_date'])->startOfDay()
                    : $today->subMonth()->startOfDay(),
                ! empty($filters['to_date'])
                    ? CarbonImmutable::parse($filters['to_date'])->endOfDay()
                    : $today->endOfDay(),
            ],
        };
    }
}
