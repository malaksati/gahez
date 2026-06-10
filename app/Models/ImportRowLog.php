<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRowLog extends Model
{
    protected $fillable = [
        'data_transfer_batch_id',
        'row_number',
        'row_data',
        'errors',
    ];

    protected function casts(): array
    {
        return [
            'row_data' => 'array',
            'errors' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(DataTransferBatch::class, 'data_transfer_batch_id');
    }
}
