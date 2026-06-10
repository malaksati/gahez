<?php

namespace App\V1\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stores uploads without relying on {@see UploadedFile::getRealPath()},
 * which is often false on Windows for PHP temp files.
 */
final class UploadStorage
{
    public static function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $directory = trim($directory, '/');
        $relativePath = $directory.'/'.$file->hashName();
        $source = $file->getRealPath() ?: $file->getPathname();

        if ($source === '' || ! is_readable($source)) {
            throw new \InvalidArgumentException('Uploaded file path is not readable.');
        }

        $stream = fopen($source, 'r');

        try {
            Storage::disk($disk)->put($relativePath, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $relativePath;
    }
}
