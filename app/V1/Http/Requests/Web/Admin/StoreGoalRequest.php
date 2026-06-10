<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\GoalValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class StoreGoalRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return GoalValidation::store();
    }
}
