<?php

namespace App\V1\DataTransfer\Exports;

use App\Models\VariantOption;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VariantOptionsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected Collection $variantOptions,
    ) {}

    public function collection(): Collection
    {
        return $this->variantOptions;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['id', 'variant_id', 'name_en', 'name_ar', 'code'];
    }

    /**
     * @param  VariantOption  $option
     * @return list<mixed>
     */
    public function map($option): array
    {
        return [
            $option->id,
            $option->variant_id,
            $option->getTranslation('name', 'en', false),
            $option->getTranslation('name', 'ar', false),
            $option->code,
        ];
    }
}
