<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;


class ProductsImport implements ToModel, WithHeadingRow , WithValidation
{
    public function model(array $row)
    {
        $category = Category::firstOrCreate([
            'name' => $row['category']
        ]);

        $supplier = Supplier::firstOrCreate([
            'name' => $row['supplier']
        ]);

        return new Product([
            'name' => $row['name'],
            'reference' => substr($category->name , 0 , 3) . '-' . Str::random(8),
            'description' => $row['description'],
            'price' => $row['price'],
            'stock' => $row['stock'],
            'min_stock' => $row['min_stock'],
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);
    }

     public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'category' => 'required|string',
            'supplier' => 'required|string',
        ];
    }
}
