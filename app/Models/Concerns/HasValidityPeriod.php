<?php

namespace App\Models\Concerns;

trait HasValidityPeriod
{
    public function scopeValid($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $now);
            });
    }

    public function isActive(): bool
    {
        return (bool) $this->getAttribute('is_active');
    }

    public function isValid(): bool
    {
        return $this->validityStatus() === 'running';
    }

    /**
     * @return 'running'|'scheduled'|'expired'|'inactive'
     */
    public function validityStatus(): string
    {
        if (! $this->getAttribute('is_active')) {
            return 'inactive';
        }

        $today = now()->toDateString();

        if ($this->getAttribute('start_date') && $this->getAttribute('start_date')->toDateString() > $today) {
            return 'scheduled';
        }

        if ($this->getAttribute('end_date') && $this->getAttribute('end_date')->toDateString() < $today) {
            return 'expired';
        }

        return 'running';
    }
}
