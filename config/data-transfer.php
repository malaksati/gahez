<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Run imports synchronously
    |--------------------------------------------------------------------------
    |
    | When true, import jobs run immediately in the request (no queue worker).
    | When false, imports are queued — run `php artisan queue:work`.
    | Exports always run immediately (never queued).
    |
    */
    'run_import_sync' => env('DATA_TRANSFER_SYNC', true),

    'chunk_size' => (int) env('DATA_TRANSFER_CHUNK_SIZE', 250),

    /*
    |--------------------------------------------------------------------------
    | Skip missing related records
    |--------------------------------------------------------------------------
    |
    | When true (zew-backend style), optional foreign keys are ignored if they
    | cannot be resolved — the row still imports:
    |   - Category parent name/id not found → imported as root
    |   - Product category id/name not found → skipped for that product
    |   - Product brand id/name not found → falls back to default_brand_id
    |   - Variant / option id not found → treated as a new record
    |
    | When false, missing optional relations are logged and the row fails where
    | noted below.
    |
    */
    'skip_missing_relations' => env('DATA_TRANSFER_SKIP_MISSING_RELATIONS', true),

    /*
    |--------------------------------------------------------------------------
    | Skip rows that already exist
    |--------------------------------------------------------------------------
    |
    | When true, import does not insert or update records that already exist in
    | the database (matched by slug, SKU, English name, option code, etc.).
    | Spreadsheet "id" / "option_id" columns are stripped on import (database
    | IDs are always auto-generated). Duplicate rows within the same file are
    | skipped as well.
    |
    */
    'skip_existing_records' => env('DATA_TRANSFER_SKIP_EXISTING', true),

    /*
    | Used when brand_id is empty or invalid and skip_missing_relations is true.
    | Falls back to the first brand in the database when null.
    */
    'default_brand_id' => env('DATA_TRANSFER_DEFAULT_BRAND_ID'),

];
