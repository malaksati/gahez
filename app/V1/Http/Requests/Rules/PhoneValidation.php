<?php

namespace App\V1\Http\Requests\Rules;

use Illuminate\Foundation\Http\FormRequest;

final class PhoneValidation
{
    public const NORMALIZED_PATTERN = '^\+201[0125][0-9]{8}$';

    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '20') && strlen($digits) === 12) {
            $normalized = '+'.$digits;
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $normalized = '+20'.substr($digits, 1);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $normalized = '+20'.$digits;
        } else {
            return null;
        }

        if (! preg_match('/'.self::NORMALIZED_PATTERN.'/', $normalized)) {
            return null;
        }

        return $normalized;
    }

    /**
     * @return list<string>
     */
    public static function rules(bool $required = false): array
    {
        $presence = $required ? 'required' : 'nullable';

        return [
            $presence,
            'string',
            'regex:/'.self::NORMALIZED_PATTERN.'/',
        ];
    }

    public static function message(): string
    {
        return __('messages.Invalid Egypt phone number');
    }

    public static function prepareRequest(FormRequest $request, array $keys): void
    {
        $merge = [];

        foreach ($keys as $key) {
            if (! $request->has($key)) {
                continue;
            }

            $value = $request->input($key);

            if ($value === null || $value === '') {
                $merge[$key] = null;

                continue;
            }

            $normalized = self::normalize((string) $value);
            $merge[$key] = $normalized ?? (string) $value;
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    public static function prepareNested(FormRequest $request, string $parent, string $child): void
    {
        $parentData = $request->input($parent);

        if (! is_array($parentData) || ! array_key_exists($child, $parentData)) {
            return;
        }

        $value = $parentData[$child];

        if ($value === null || $value === '') {
            $parentData[$child] = null;
        } else {
            $normalized = self::normalize((string) $value);
            $parentData[$child] = $normalized ?? (string) $value;
        }

        $request->merge([
            $parent => $parentData,
        ]);
    }
}
