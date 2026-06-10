<?php

namespace App\V1\DataTransfer\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VariantsTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect();
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return (new VariantsExport(collect()))->headings();
    }
}
