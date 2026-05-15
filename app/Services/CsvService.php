<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

interface CsvServiceInterface
{
    public function exportcsv(Collection $products);
    public function importCsv($file);
}

class CsvService implements CsvServiceInterface
{
    public function exportCsv(Collection $products)
    {
        $fileName = 'products.csv';

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $callback = function () use ($products) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Reference',
                'Name',
                'description',
                'Price',
                'Stock',
                'Min stock',
                'Category',
                'Supplier',
                'Created at'
            ]);


            foreach ($products as $product) {

                fputcsv($file, [
                    $product->id,
                    $product->reference,
                    $product->name,
                    $product->description,
                    $product->price,
                    $product->stock,
                    $product->min_stock,
                    $product->category?->name,
                    $product->supplier?->name,
                    $product->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importCsv($file)
    {


        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {

            $category = Category::firstOrCreate([
                'name' => $row[6]
            ]);

            $suplier = Supplier::firstOrCreate([
                'name' => $row[7],
            ]);

           $product = Product::create([

                'name' => $row[1],
                'description' => $row[2],
                'price' => $row[3],
                'stock' => $row[4],
                'Min stock' => $row[5],
                'category_id' => $category->id ,
                'supplier' => $suplier->id ,
            ]);
        }

        fclose($file);

        return $product ; 
    }
}
