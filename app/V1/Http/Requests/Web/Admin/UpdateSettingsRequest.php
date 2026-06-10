<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateSettingsRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'max:10'],
            'cashback_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'point_to_value' => ['nullable', 'numeric', 'min:0'],
            'report_hero_order_amount' => ['nullable', 'numeric', 'min:0'],
            'report_lower_value_order_amount' => ['nullable', 'numeric', 'min:0'],
            'app_logo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:10240'],
        ];
    }
}
