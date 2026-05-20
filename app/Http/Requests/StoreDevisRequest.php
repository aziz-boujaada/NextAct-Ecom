<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDevisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'reference' => ['prohibited'],
            'subtotal' => ['prohibited'],
            'total' => ['prohibited'],
            'created_by' => ['prohibited'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'sent', 'rejected', 'expired'])],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date' , 'after_or_equal:now'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
