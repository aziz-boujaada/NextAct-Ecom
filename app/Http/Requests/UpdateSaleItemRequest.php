<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['sometimes', 'required', 'integer', 'exists:sales,id'],
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'price' => ['prohibited'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
