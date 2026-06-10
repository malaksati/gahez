<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;

class ImportSpreadsheetRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
        ];
    }
}
