<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

class PurchaseItemService
{

    public function productForPurchase(int $purchaseId, int $productId): Product
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $product = Product::findOrFail($productId);

        if ((int) $product->supplier_id !== (int) $purchase->supplier_id) {
            throw ValidationException::withMessages([
                'product_id' => ['The selected product does not belong to the purchase supplier.'],
            ]);
        }

        return $product;
    }

    public function applyStockMovement(Product $product, int $quantity, string $type, int $purchaseItemId): void
    {
        $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
        
            if ($type === 'in') {
                $product->increment('stock', $quantity);
            } else {
                $product->decrement('stock', $quantity);
            }

            StockMovement::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'type' => $type,
                'source' => 'purchase',
                'reference_id' => $purchaseItemId,
            ]); 
    }

    public function refreshPurchaseTotal(Purchase $purchase): void
    {
        $purchase->update([
            'total' => $purchase->items()->sum('total'),
        ]);
    }
}
