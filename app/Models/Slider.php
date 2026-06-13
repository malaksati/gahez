<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    public const TYPE_HOME = 'home';

    public const TYPE_CATEGORY = 'category';

    public const TYPE_BRAND = 'brand';

    public const TYPE_OFFER = 'offer';

    public const TYPE_PRODUCT = 'product';

    public const TYPE_COUPON = 'coupon';

    public const TYPE_GOAL = 'goal';

    public const TYPE_SUPPORT_CHAT = 'support_chat';

    public const TYPE_TICKET = 'ticket';

    protected $fillable = [
        'image',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_HOME,
            self::TYPE_CATEGORY,
            self::TYPE_BRAND,
            self::TYPE_OFFER,
            self::TYPE_PRODUCT,
            self::TYPE_COUPON,
            self::TYPE_GOAL,
            self::TYPE_SUPPORT_CHAT,
            self::TYPE_TICKET,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_HOME => __('messages.Slider type home'),
            self::TYPE_CATEGORY => __('messages.Slider type category'),
            self::TYPE_BRAND => __('messages.Slider type brand'),
            self::TYPE_OFFER => __('messages.Slider type offer'),
            self::TYPE_PRODUCT => __('messages.Slider type product'),
            self::TYPE_COUPON => __('messages.Slider type coupon'),
            self::TYPE_GOAL => __('messages.Slider type goal'),
            self::TYPE_SUPPORT_CHAT => __('messages.Slider type support_chat'),
            self::TYPE_TICKET => __('messages.Slider type ticket'),
        ];
    }

    public static function typeLabel(?string $type): string
    {
        if ($type === null || $type === '') {
            return '—';
        }

        return self::typeLabels()[$type] ?? $type;
    }

    public function getImagePathAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return asset('storage/'.$this->image);
    }
}
