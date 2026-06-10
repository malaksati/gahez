<?php

namespace App\V1\DataTransfer;

use DateTimeInterface;
use Illuminate\Support\Carbon;

final class DataTransferConfig
{
    public const CHUNK_SIZE = 250; // @deprecated use config('data-transfer.chunk_size')

    /** @deprecated use config('data-transfer.skip_missing_relations') */
    public const SKIP_MISSING_RELATIONS = true;

    public const DISK = 'local';

    public const IMPORT_DIR = 'imports';

    public const EXPORT_DIR = 'exports';

    /**
     * e.g. products_export_2026-05-19_124931.xlsx
     */
    public static function exportRelativePath(string $entity, ?DateTimeInterface $at = null): string
    {
        $at = Carbon::parse($at ?? now());
        $filename = sprintf('%s_export_%s.xlsx', $entity, $at->format('Y-m-d_His'));

        return self::EXPORT_DIR.'/'.$filename;
    }
}
