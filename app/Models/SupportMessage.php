<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    /** @use HasFactory<\Database\Factories\SupportMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'support_id',
        'sender_type',
        'sender_id',
        'message',
        'attachments',
        'read_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'read_at' => 'datetime',
    ];

    public function support(): BelongsTo
    {
        return $this->belongsTo(Support::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * @return list<string>|null
     */
    public function attachmentUrls(): ?array
    {
        if (! $this->attachments) {
            return null;
        }

        return collect($this->attachments)
            ->map(fn (string $path) => asset('storage/'.ltrim($path, '/')))
            ->values()
            ->all();
    }
}
