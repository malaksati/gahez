<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Support\StoreTheme;
use Illuminate\Validation\Rule;

class UpdateStoreThemeRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'store_primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'store_secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'store_category_layout' => ['required', Rule::in(StoreTheme::layoutOptions())],
            'store_product_layout' => ['required', Rule::in(StoreTheme::layoutOptions())],
            'store_font_family' => ['required', Rule::in(StoreTheme::fontOptions())],
        ];
    }
}
