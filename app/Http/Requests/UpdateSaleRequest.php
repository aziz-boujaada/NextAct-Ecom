<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'required', 'integer', 'exists:clients,id'],
            'total' => ['prohibited'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['paid', 'unpaid', 'refunded'])],
        ];
    }
}
