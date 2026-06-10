<?php

namespace App\V1\DataTransfer\Support;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ImportRelationResolver
{
    /** @var Collection<int, Category>|null */
    private static ?Collection $categories = null;

    /** @var array<int, Category> */
    private static array $categoriesById = [];

    /** @var array<string, Category> */
    private static array $categoriesByNameEn = [];

    /** @var array<string, Category> */
    private static array $categoriesBySlug = [];

    /** @var array<int, Brand> */
    private static array $brandsById = [];

    /** @var array<string, Brand> */
    private static array $brandsByNameEn = [];

    private static bool $brandsLoaded = false;

    public static function shouldSkipMissingRelations(): bool
    {
        return (bool) config('data-transfer.skip_missing_relations', true);
    }

    public static function resolveCategoryParentId(?string $reference, ?string $childSlug = null): ?int
    {
        $reference = trim((string) $reference);

        if ($reference === '' || self::isEmptyParentReference($reference)) {
            return null;
        }

        self::loadCategories();

        if (preg_match('/^(\d+)\s*\(/', $reference, $matches)) {
            $parentId = (int) $matches[1];

            return self::acceptParentId($parentId, $childSlug);
        }

        if (is_numeric($reference)) {
            return self::acceptParentId((int) $reference, $childSlug);
        }

        $parentName = strtolower($reference);

        if (isset(self::$categoriesByNameEn[$parentName])) {
            return self::acceptParentId(self::$categoriesByNameEn[$parentName]->id, $childSlug);
        }

        $slug = Str::slug($reference);

        $parentId = Category::query()
            ->where('slug', $slug)
            ->orWhere('slug', $reference)
            ->orWhere('name->en', $reference)
            ->orWhere('name->ar', $reference)
            ->value('id');

        return $parentId ? self::acceptParentId((int) $parentId, $childSlug) : null;
    }

    /**
     * @return list<int>
     */
    public static function resolveCategoryIds(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        self::loadCategories();

        $items = is_array($value)
            ? $value
            : (preg_split('/[|,;]/', (string) $value) ?: []);

        $categoryIds = [];

        foreach ($items as $item) {
            $item = trim((string) $item);

            if ($item === '') {
                continue;
            }

            $category = self::findCategoryByReference($item);

            if ($category !== null) {
                $categoryIds[] = $category->id;
            } elseif (! self::shouldSkipMissingRelations()) {
                Log::warning('Import skipped unknown category name', ['category' => $item]);
            }
        }

        return array_values(array_unique($categoryIds));
    }

    public static function findCategoryByReference(string $reference): ?Category
    {
        $reference = trim($reference);

        if ($reference === '') {
            return null;
        }

        self::loadCategories();

        if (is_numeric($reference)) {
            $categoryId = (int) $reference;

            return self::$categoriesById[$categoryId] ?? null;
        }

        $normalized = strtolower($reference);

        if (isset(self::$categoriesByNameEn[$normalized])) {
            return self::$categoriesByNameEn[$normalized];
        }

        $slug = Str::slug($reference);

        if ($slug !== '' && isset(self::$categoriesBySlug[$slug])) {
            return self::$categoriesBySlug[$slug];
        }

        return null;
    }

    public static function resolveBrandId(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        self::loadBrands();

        if ($value !== '' && is_numeric($value)) {
            $brandId = (int) $value;

            if (isset(self::$brandsById[$brandId])) {
                return $brandId;
            }
        } elseif ($value !== '') {
            $brandName = strtolower($value);

            if (isset(self::$brandsByNameEn[$brandName])) {
                return self::$brandsByNameEn[$brandName]->id;
            }
        }

        if (! self::shouldSkipMissingRelations()) {
            return null;
        }

        $defaultId = config('data-transfer.default_brand_id');

        if ($defaultId && isset(self::$brandsById[(int) $defaultId])) {
            return (int) $defaultId;
        }

        $firstBrandId = Brand::query()->orderBy('id')->value('id');

        return $firstBrandId ? (int) $firstBrandId : null;
    }

    private static function acceptParentId(int $parentId, ?string $childSlug): ?int
    {
        if (! isset(self::$categoriesById[$parentId])) {
            return null;
        }

        if ($childSlug !== null && self::$categoriesById[$parentId]->slug === $childSlug) {
            return null;
        }

        return $parentId;
    }

    private static function isEmptyParentReference(string $reference): bool
    {
        return in_array(strtolower(trim($reference)), [
            'root',
            'none',
            'null',
            '-',
            '—',
            'n/a',
            'na',
        ], true);
    }

    private static function loadCategories(): void
    {
        if (self::$categories !== null) {
            return;
        }

        self::$categories = Category::query()->select(['id', 'name', 'slug'])->get();
        self::$categoriesById = [];
        self::$categoriesByNameEn = [];
        self::$categoriesBySlug = [];

        foreach (self::$categories as $category) {
            self::$categoriesById[$category->id] = $category;

            foreach (['en', 'ar'] as $locale) {
                $name = strtolower(trim((string) $category->getTranslation('name', $locale, false)));

                if ($name !== '' && ! isset(self::$categoriesByNameEn[$name])) {
                    self::$categoriesByNameEn[$name] = $category;
                }
            }

            $slug = strtolower(trim((string) $category->slug));

            if ($slug !== '' && ! isset(self::$categoriesBySlug[$slug])) {
                self::$categoriesBySlug[$slug] = $category;
            }
        }
    }

    private static function loadBrands(): void
    {
        if (self::$brandsLoaded) {
            return;
        }

        self::$brandsLoaded = true;
        self::$brandsById = [];
        self::$brandsByNameEn = [];

        foreach (Brand::query()->select(['id', 'name'])->get() as $brand) {
            self::$brandsById[$brand->id] = $brand;

            $nameEn = strtolower(trim((string) $brand->getTranslation('name', 'en', false)));

            if ($nameEn !== '') {
                self::$brandsByNameEn[$nameEn] = $brand;
            }
        }
    }

    public static function resetCaches(): void
    {
        self::$categories = null;
        self::$categoriesById = [];
        self::$categoriesByNameEn = [];
        self::$categoriesBySlug = [];
        self::$brandsById = [];
        self::$brandsByNameEn = [];
        self::$brandsLoaded = false;
    }
}
