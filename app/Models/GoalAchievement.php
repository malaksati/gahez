<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalAchievement extends Model
{
    protected $fillable = [
        'goal_id',
        'user_id',
        'period_start',
        'period_end',
        'order_total',
        'reward_amount',
        'awarded_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'order_total' => 'decimal:2',
        'reward_amount' => 'decimal:2',
        'awarded_at' => 'datetime',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
