<?php

namespace App\V1\DataTransfer\Exports;

use App\Models\Category;
use App\V1\DataTransfer\Support\CategorySpreadsheetColumns;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected Collection $categories,
    ) {}

    public function collection(): Collection
    {
        return $this->categories;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return CategorySpreadsheetColumns::headings();
    }

    /**
     * @param  Category  $category
     * @return list<mixed>
     */
    public function map($category): array
    {
        return [
            $category->id,
            $category->getTranslation('name', 'en', false),
            $category->getTranslation('name', 'ar', false),
            $category->slug,
            $category->getRawOriginal('image'),
            $category->is_active ? 1 : 0,
            $category->is_featured ? 1 : 0,
            $category->sort_order,
            $category->parent_id,
            $category->parent?->slug,
        ];
    }
}
