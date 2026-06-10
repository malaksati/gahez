<?php

namespace App\V1\DataTransfer\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use App\Models\VariantOption;

/**
 * Skips import rows when a record already exists (DB) or was already queued in the same file.
 * Spreadsheet IDs are ignored — matching uses slug, SKU, name, option code, etc.
 */
final class ImportDuplicateGuard
{
    /** @var array<string, true> */
    private array $seenCategoryKeys = [];

    /** @var array<string, true> */
    private array $seenProductKeys = [];

    /** @var array<string, true> */
    private array $seenOptionCodes = [];

    public static function shouldSkipExisting(): bool
    {
        return (bool) config('data-transfer.skip_existing_records', true);
    }

    /**
     * @param  array{name: array{en: string, ar: string}, slug: string}  $payload
     */
    public function shouldSkipCategory(array $payload): bool
    {
        if (! self::shouldSkipExisting()) {
            $this->rememberCategory($payload);

            return false;
        }

        $slug = trim((string) ($payload['slug'] ?? ''));
        $nameEn = $this->normalizedNameKey($payload['name']['en'] ?? '');

        if ($slug !== '' && isset($this->seenCategoryKeys['slug:'.$slug])) {
            return true;
        }

        if ($nameEn !== '' && isset($this->seenCategoryKeys['name:'.$nameEn])) {
            return true;
        }

        if ($slug !== '' && Category::withTrashed()->where('slug', $slug)->exists()) {
            return true;
        }

        if ($nameEn !== '' && $this->categoryExistsByEnglishName($payload['name']['en'])) {
            return true;
        }

        $this->rememberCategory($payload);

        return false;
    }

    /**
     * @param  array<string, mixed>  $product
     */
    public function shouldSkipProduct(array $product): bool
    {
        if (! self::shouldSkipExisting()) {
            $this->rememberProduct($product);

            return false;
        }

        $sku = trim((string) ($product['sku'] ?? ''));
        $slug = trim((string) ($product['slug'] ?? ''));
        $nameEn = $this->normalizedNameKey($product['name']['en'] ?? '');

        foreach ($this->productKeys($sku, $slug, $nameEn) as $key) {
            if (isset($this->seenProductKeys[$key])) {
                return true;
            }
        }

        if ($sku !== '' && Product::withTrashed()->where('sku', $sku)->exists()) {
            return true;
        }

        if ($slug !== '' && Product::withTrashed()->where('slug', $slug)->exists()) {
            return true;
        }

        if ($nameEn !== '' && $this->productExistsByEnglishName($product['name']['en'])) {
            return true;
        }

        $this->rememberProduct($product);

        return false;
    }

    /**
     * @param  array{name: array{en: string, ar: string}}  $variant
     */
    public function shouldSkipVariant(array $variant): bool
    {
        if (! self::shouldSkipExisting()) {
            return false;
        }

        $nameEn = trim((string) ($variant['name']['en'] ?? ''));

        if ($nameEn === '') {
            return false;
        }

        return $this->variantExistsByEnglishName($nameEn);
    }

    public function shouldSkipVariantOption(?string $code): bool
    {
        $code = trim((string) $code);

        if ($code === '') {
            return false;
        }

        if (! self::shouldSkipExisting()) {
            $this->seenOptionCodes['code:'.$code] = true;

            return false;
        }

        if (isset($this->seenOptionCodes['code:'.$code])) {
            return true;
        }

        if (VariantOption::withTrashed()->where('code', $code)->exists()) {
            return true;
        }

        $this->seenOptionCodes['code:'.$code] = true;

        return false;
    }

    /**
     * @param  array{name: array{en: string, ar: string}, slug: string}  $payload
     */
    private function rememberCategory(array $payload): void
    {
        $slug = trim((string) ($payload['slug'] ?? ''));
        $nameEn = $this->normalizedNameKey($payload['name']['en'] ?? '');

        if ($slug !== '') {
            $this->seenCategoryKeys['slug:'.$slug] = true;
        }

        if ($nameEn !== '') {
            $this->seenCategoryKeys['name:'.$nameEn] = true;
        }
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function rememberProduct(array $product): void
    {
        $sku = trim((string) ($product['sku'] ?? ''));
        $slug = trim((string) ($product['slug'] ?? ''));
        $nameEn = $this->normalizedNameKey($product['name']['en'] ?? '');

        foreach ($this->productKeys($sku, $slug, $nameEn) as $key) {
            $this->seenProductKeys[$key] = true;
        }
    }

    /**
     * @return list<string>
     */
    private function productKeys(string $sku, string $slug, string $nameEn): array
    {
        $keys = [];

        if ($sku !== '') {
            $keys[] = 'sku:'.$sku;
        }

        if ($slug !== '') {
            $keys[] = 'slug:'.$slug;
        }

        if ($nameEn !== '') {
            $keys[] = 'name:'.$nameEn;
        }

        return $keys;
    }

    private function normalizedNameKey(mixed $name): string
    {
        return mb_strtolower(trim((string) $name));
    }

    private function categoryExistsByEnglishName(string $nameEn): bool
    {
        $nameEn = trim($nameEn);

        if ($nameEn === '') {
            return false;
        }

        return Category::withTrashed()
            ->where('name->en', $nameEn)
            ->exists();
    }

    private function productExistsByEnglishName(string $nameEn): bool
    {
        $nameEn = trim($nameEn);

        if ($nameEn === '') {
            return false;
        }

        return Product::withTrashed()
            ->where('name->en', $nameEn)
            ->exists();
    }

    private function variantExistsByEnglishName(string $nameEn): bool
    {
        $nameEn = trim($nameEn);

        if ($nameEn === '') {
            return false;
        }

        return Variant::withTrashed()
            ->where('name->en', $nameEn)
            ->exists();
    }
}
