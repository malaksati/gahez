<?php

namespace App\V1\Http\Resources\Api;

use App\Models\Goal;
use App\V1\Http\Resources\Concerns\LocalizesTranslatableAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    use LocalizesTranslatableAttributes;

    /**
     * @param  array<string, mixed>|Goal  $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Goal) {
            return $this->fromGoalModel($request, $this->resource);
        }

        /** @var array<string, mixed> $row */
        $row = $this->resource;
        $goal = $row['goal'];

        if (! $goal instanceof Goal) {
            return [];
        }

        return array_merge($this->fromGoalModel($request, $goal), [
            'period_start' => $row['period_start'],
            'period_end' => $row['period_end'],
            'order_total' => (float) $row['order_total'],
            'min_order_total' => (float) $row['min_order_total'],
            'reward_amount' => (float) $row['reward_amount'],
            'progress_percent' => (float) $row['progress_percent'],
            'is_achieved' => (bool) $row['is_achieved'],
            'achieved_at' => $row['achieved_at'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromGoalModel(Request $request, Goal $goal): array
    {
        $description = $goal->getTranslations('description');
        $localizedDescription = $this->localizedValue($description, null, $request);

        if ($localizedDescription === null || trim($localizedDescription) === '') {
            $localizedDescription = __('messages.Goal cashback description', [
                'reward' => number_format((float) $goal->reward_amount, 2),
                'target' => number_format((float) $goal->min_order_total, 2),
                'currency' => app_currency(),
            ]);
        }

        return [
            'id' => $goal->id,
            'name' => $this->localizedValue($goal->getTranslations('name'), null, $request),
            'description' => $localizedDescription,
            'period_type' => $goal->period_type,
            'sort_order' => (int) $goal->sort_order,
        ];
    }
}
