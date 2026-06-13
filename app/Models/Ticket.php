<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public const TYPE_COMPLAINT = 'complaint';

    public const TYPE_RECOMMENDATION = 'recommendation';

    protected $fillable = ['user_id', 'type', 'subject', 'description', 'status', 'attachments'];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_COMPLAINT,
            self::TYPE_RECOMMENDATION,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_COMPLAINT => __('messages.ticket_type_complaint'),
            self::TYPE_RECOMMENDATION => __('messages.ticket_type_recommendation'),
        ];
    }

    public static function typeLabel(?string $type): string
    {
        $type = $type ?: self::TYPE_COMPLAINT;

        return self::typeLabels()[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    public function getAttachmentsPathAttribute()
    {
        return $this->attachments ? collect($this->attachments)->map(function ($attachment) {
            return asset('storage/'.$attachment);
        }) : null;
    }
}
