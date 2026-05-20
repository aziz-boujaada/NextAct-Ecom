<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDevisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'nullable', 'integer', 'exists:clients,id'],
            'reference' => ['prohibited'],
            'subtotal' => ['prohibited'],
            'total' => ['prohibited'],
            'created_by' => ['prohibited'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'sent', 'rejected', 'expired'])],
            'discount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'tax' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:now'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'items' => ['sometimes', 'array'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
