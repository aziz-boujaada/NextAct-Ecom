<?php
namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::with(['category', 'supplier'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Price',
            'Stock',
            'Min Stock',
            'Category',
            'Supplier',
            'Created At'
        ];
    }
}