<?php

namespace App\Models;

use App\V1\DataTransfer\DataTransferConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class DataTransferBatch extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const ENTITY_PRODUCTS = 'products';

    public const ENTITY_CATEGORIES = 'categories';

    public const ENTITY_VARIANTS = 'variants';

    public const ENTITY_VARIANT_OPTIONS = 'variant_options';

    public const DIRECTION_IMPORT = 'import';

    public const DIRECTION_EXPORT = 'export';

    protected $fillable = [
        'user_id',
        'entity',
        'direction',
        'status',
        'file_path',
        'total_rows',
        'processed_rows',
        'success_count',
        'failed_count',
        'skipped_count',
        'message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rowLogs(): HasMany
    {
        return $this->hasMany(ImportRowLog::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
    }

    public function hasDownloadableFile(): bool
    {
        if ($this->direction !== self::DIRECTION_EXPORT || $this->status !== self::STATUS_COMPLETED) {
            return false;
        }

        $path = $this->file_path;

        if (! is_string($path) || trim($path) === '') {
            return false;
        }

        return Storage::disk(DataTransferConfig::DISK)->exists($path);
    }
}
