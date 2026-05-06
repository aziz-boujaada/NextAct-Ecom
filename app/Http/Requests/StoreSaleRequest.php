<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'reference' => ['prohibited'],
            'total' => ['prohibited'],
            'status' => ['sometimes', 'string', Rule::in(['paid', 'unpaid', 'refunded'])],
        ];
    }
}
