<?php

namespace App\V1\Http\Requests\Rules;

final class AddressValidation
{
    /**
     * @return array<string, list<string>>
     */
    public static function store(): array
    {
        return [
            'user_id' => ['prohibited'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'string', 'max:32'],
            'longitude' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function update(): array
    {
        return [
            'user_id' => ['prohibited'],
            'address' => ['sometimes', 'string', 'max:500'],
            'latitude' => ['sometimes', 'string', 'max:32'],
            'longitude' => ['sometimes', 'string', 'max:32'],
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function nestedForCustomer(): array
    {
        return [
            'address.id' => ['nullable', 'integer', 'exists:addresses,id'],
            'address.name' => ['nullable', 'required_with:address.address', 'string', 'max:255'],
            'address.address' => ['nullable', 'required_with:address.name', 'string', 'max:500'],
            'address.latitude' => ['nullable', 'required_with:address.address', 'string', 'max:32'],
            'address.longitude' => ['nullable', 'required_with:address.address', 'string', 'max:32'],
            'address.phone' => ['nullable', 'string', 'max:50'],
            'address.city' => ['nullable', 'string', 'max:120'],
            'address.state' => ['nullable', 'string', 'max:120'],
            'address.is_default' => ['nullable', 'boolean'],
        ];
    }
}
