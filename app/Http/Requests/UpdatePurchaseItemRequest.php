<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_id' => ['sometimes', 'required', 'integer', 'exists:purchases,id'],
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'price' => ['prohibited'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
