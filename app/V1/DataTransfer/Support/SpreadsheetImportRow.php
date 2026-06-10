<?php

namespace App\V1\DataTransfer\Support;

/**
 * Normalizes import rows: spreadsheet primary keys are never used on import.
 */
final class SpreadsheetImportRow
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function withoutGeneratedIds(array $row): array
    {
        unset($row['id']);

        return $row;
    }

    /**
     * Variant option rows may include an exported option primary key.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function withoutGeneratedVariantOptionIds(array $row): array
    {
        unset($row['option_id']);

        return $row;
    }
}
