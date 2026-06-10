<?php

namespace App\V1\DataTransfer\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class EmptyArrayImport implements ToArray
{
    public function array(array $array): array
    {
        return $array;
    }
}
