<?php

namespace App\V1\Http\Requests\Concerns;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait MapsOfferableInput
{
    /**
     * @return array<string, class-string>
     */
    protected function offerableTypeMap(): array
    {
        return [
            'product' => Product::class,
            'category' => Category::class,
        ];
    }

    protected function prepareOfferableInput(): void
    {
        $type = (string) $this->input('type');

        if (in_array($type, ['threshold_gift', 'free_delivery'], true)) {
            $this->merge([
                'offerable_type' => null,
                'offerable_id' => null,
            ]);

            return;
        }

        if (! $this->filled('offerable_type_key')) {
            return;
        }

        $key = (string) $this->input('offerable_type_key');
        $map = $this->offerableTypeMap();

        if (isset($map[$key])) {
            $this->merge(['offerable_type' => $map[$key]]);
        }
    }

    /**
     * @param  array<string, list<mixed>>  $rules
     * @return array<string, list<mixed>>
     */
    protected function withOfferableRules(array $rules, bool $required = true): array
    {
        $type = (string) $this->input('type');

        if (in_array($type, ['threshold_gift', 'free_delivery'], true)) {
            unset($rules['offerable_type'], $rules['offerable_id']);

            return $rules;
        }

        $typeKeyRule = $required
            ? ['required', Rule::in(array_keys($this->offerableTypeMap()))]
            : ['sometimes', Rule::in(array_keys($this->offerableTypeMap()))];

        $rules['offerable_type_key'] = $typeKeyRule;

        $key = (string) $this->input('offerable_type_key', 'product');
        $table = $key === 'category' ? 'categories' : 'products';

        $idRules = $required
            ? ['required', 'integer', Rule::exists($table, 'id')]
            : ['sometimes', 'integer', Rule::exists($table, 'id')];

        $rules['offerable_id'] = $idRules;

        return $rules;
    }

    protected function validateOfferBusinessRules(Validator $validator): void
    {
        $type = (string) $this->input('type');

        if ($type === 'bogo') {
            $buyQty = (int) $this->input('bogo_buy_quantity', 1);
            $bonusQty = (int) $this->input('bogo_bonus_quantity', 1);
            $discountType = (string) $this->input('bogo_bonus_discount_type', 'percentage');
            $discountValue = $this->input('bogo_bonus_discount_value');

            if ($buyQty < 1 || $buyQty > 2) {
                $validator->errors()->add('bogo_buy_quantity', __('messages.BOGO buy quantity must be 1 or 2.'));
            }

            if ($bonusQty < 1 || $bonusQty > 2) {
                $validator->errors()->add('bogo_bonus_quantity', __('messages.BOGO bonus quantity must be 1 or 2.'));
            }

            if (! in_array($discountType, ['percentage', 'fixed'], true)) {
                $validator->errors()->add('bogo_bonus_discount_type', __('messages.BOGO bonus discount type is invalid.'));
            }

            if ($discountValue === null || $discountValue === '') {
                $validator->errors()->add('bogo_bonus_discount_value', __('messages.BOGO bonus discount value is required.'));
            } elseif ($discountType === 'percentage' && ((float) $discountValue < 0 || (float) $discountValue > 100)) {
                $validator->errors()->add('bogo_bonus_discount_value', __('messages.BOGO bonus percentage must be between 0 and 100.'));
            }
        }

        if (in_array($type, ['fixed', 'percentage'], true) && ! $this->filled('value')) {
            $validator->errors()->add('value', __('messages.Discount value is required.'));
        }

        if (in_array($type, ['threshold_gift', 'free_delivery'], true) && ! $this->filled('min_cart_amount')) {
            $validator->errors()->add('min_cart_amount', __('messages.Minimum cart amount is required.'));
        }

        if ($type === 'threshold_gift') {
            $rewardIds = array_filter((array) $this->input('reward_product_ids', []));

            if (count($rewardIds) < 1) {
                $validator->errors()->add('reward_product_ids', __('messages.Select at least one gift product.'));
            }
        }
    }
}
