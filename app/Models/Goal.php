<?php

namespace App\Models;

use App\Models\Concerns\HasValidityPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Goal extends Model
{
    use HasTranslations, HasValidityPeriod;

    public const PERIOD_DAILY = 'daily';

    public const PERIOD_WEEKLY = 'weekly';

    public const PERIOD_MONTHLY = 'monthly';

    protected $fillable = [
        'name',
        'description',
        'period_type',
        'min_order_total',
        'reward_amount',
        'start_date',
        'end_date',
        'is_active',
        'sort_order',
    ];

    protected $translatable = ['name', 'description'];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'min_order_total' => 'decimal:2',
        'reward_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function achievements(): HasMany
    {
        return $this->hasMany(GoalAchievement::class);
    }

    /**
     * @return list<string>
     */
    public static function periodTypes(): array
    {
        return [
            self::PERIOD_DAILY,
            self::PERIOD_WEEKLY,
            self::PERIOD_MONTHLY,
        ];
    }
}
