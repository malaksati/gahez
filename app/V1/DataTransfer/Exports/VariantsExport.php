<?php

namespace App\V1\DataTransfer\Exports;

use App\V1\DataTransfer\Support\VariantSpreadsheetColumns;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VariantsExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Collection $rows,
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return VariantSpreadsheetColumns::headings();
    }
}
