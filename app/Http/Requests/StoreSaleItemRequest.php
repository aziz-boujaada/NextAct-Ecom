<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'price' => ['prohibited'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
