<?php

namespace App\V1\DataTransfer\Exports;

use App\Models\Product;
use App\V1\DataTransfer\Support\ProductSpreadsheetColumns;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected Collection $products,
    ) {}

    public function collection(): Collection
    {
        return $this->products;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ProductSpreadsheetColumns::headings();
    }

    /**
     * @param  Product  $product
     * @return list<mixed>
     */
    public function map($product): array
    {
        $product->loadMissing('categories');
        $categoryNames = $product->categories->isNotEmpty()
            ? $product->categories
                ->map(fn ($category) => $category->getTranslation('name', 'en', false) ?: $category->getTranslation('name', 'ar', false))
                ->filter()
                ->values()
                ->all()
            : collect($product->category_snapshot ?? [])
                ->map(fn ($row) => $row['name'] ?? ($row['name_ar'] ?? null))
                ->filter()
                ->values()
                ->all();

        return [
            $product->id,
            $product->getTranslation('name', 'en', false),
            $product->getTranslation('name', 'ar', false),
            $product->getTranslation('description', 'en', false),
            $product->getTranslation('description', 'ar', false),
            $product->type,
            $product->sku,
            $product->slug,
            $product->price,
            $product->stock,
            $product->is_in_stock ? 1 : 0,
            $product->sort_order,
            $product->discount,
            $product->discount_type,
            $product->getRawOriginal('thumbnail'),
            $product->brand_id,
            $product->displayBrandName('en'),
            $categoryNames !== [] ? implode('|', $categoryNames) : null,
            $product->is_active ? 1 : 0,
            $product->is_featured ? 1 : 0,
            $product->is_new ? 1 : 0,
            $product->is_approved ? 1 : 0,
            $product->is_bookable ? 1 : 0,
        ];
    }
}
