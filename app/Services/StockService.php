<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function move(Product $product, int $quantity, string $type, string $source, ?int $referenceId = null): Product
    {
        $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

        if ($type === 'out' && $product->stock < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ['The requested quantity is greater than the available stock.'],
            ]);
        }

        if ($type === 'in') {
            $product->increment('stock', $quantity);
        } else {
            $product->decrement('stock', $quantity);
        }

        StockMovement::create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'type' => $type,
            'source' => $source,
            'reference_id' => $referenceId,
        ]);

        return $product->fresh();
    }
}
