<?php

namespace App\V1\Http\Requests\Web;

use App\V1\Http\Requests\Concerns\HasCustomValidationMessages;
use Illuminate\Foundation\Http\FormRequest;

abstract class AdminFormRequest extends FormRequest
{
    use HasCustomValidationMessages;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
