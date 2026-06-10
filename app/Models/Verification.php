<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'target',
        'code',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function markAsVerified(): void
    {
        $this->update([
            'verified_at' => now(),
        ]);
    }

    public function scopeValid($query)
    {
        return $query
            ->whereNull('verified_at')
            ->where('expires_at', '>', now());
    }
}
