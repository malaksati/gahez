<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLog extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'from_status',
        'to_status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        $from = $this->formatStatus($this->from_status);
        $to = $this->formatStatus($this->to_status);

        return match ($this->type) {
            'order_placed' => __('messages.log.order_placed'),
            'status_change' => __('messages.log.status_change', ['from' => $from, 'to' => $to]),
            'payment_change' => __('messages.log.payment_change', ['from' => $from, 'to' => $to]),
            'cancelled' => __('messages.log.cancelled'),
            'refunded' => __('messages.log.refunded'),
            default => ucfirst(str_replace('_', ' ', (string) $this->type)),
        };
    }

    public function formattedType(): string
    {
        $key = 'messages.log_type.'.$this->type;

        return __($key) !== $key ? __($key) : ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    public function formattedStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return '—';
        }

        $key = 'messages.'.$status;

        return __($key) !== $key ? __($key) : ucfirst(str_replace('_', ' ', $status));
    }

    public function formattedPayload(): ?string
    {
        if ($this->payload === null || $this->payload === []) {
            return null;
        }

        return json_encode($this->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function formatStatus(?string $status): string
    {
        return $this->formattedStatus($status);
    }
}
