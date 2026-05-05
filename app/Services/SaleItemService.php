<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SaleItemService
{
    public function __construct(private readonly StockService $stockService) {}

    public function create(array $data): SaleItem
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            $data['price'] = $product->price;
            $data['total'] = $data['price'] * $data['quantity'];

            $saleItem = SaleItem::create($data);

            $this->stockService->move($product, $saleItem->quantity, 'out', 'sale', $saleItem->id);
            $this->refreshSaleTotal($saleItem->sale);

            return $saleItem->fresh(['sale.client', 'product']);
        });
    }

    public function update(SaleItem $saleItem, array $data): SaleItem
    {
        return DB::transaction(function () use ($saleItem, $data) {
            $oldSale = $saleItem->sale;
            $oldProduct = $saleItem->product;
            $oldQuantity = $saleItem->quantity;

            $product = Product::findOrFail($data['product_id'] ?? $saleItem->product_id);
            $quantity = $data['quantity'] ?? $saleItem->quantity;

            $data['price'] = $product->price;
            $data['total'] = $data['price'] * $quantity;

            $saleItem->update($data);
            $this->syncStock($oldProduct, $product, $oldQuantity, $quantity, $saleItem->id);

            $saleItem = $saleItem->fresh(['sale.client', 'product']);

            $this->refreshSaleTotal($oldSale);

            if ($oldSale->isNot($saleItem->sale)) {
                $this->refreshSaleTotal($saleItem->sale);
            }

            return $saleItem;
        });
    }

    public function delete(SaleItem $saleItem): void
    {
        DB::transaction(function () use ($saleItem) {
            $sale = $saleItem->sale;
            $product = $saleItem->product;
            $quantity = $saleItem->quantity;
            $referenceId = $saleItem->id;

            $saleItem->delete();

            $this->stockService->move($product, $quantity, 'in', 'sale', $referenceId);
            $this->refreshSaleTotal($sale);
        });
    }

    private function refreshSaleTotal(Sale $sale): void
    {
        $sale->update([
            'total' => $sale->items()->sum('total'),
        ]);
    }

    private function syncStock(
        Product $oldProduct,
        Product $newProduct,
        int $oldQuantity,
        int $newQuantity,
        int $saleItemId
    ): void {
        if ($oldProduct->isNot($newProduct)) {
            $this->stockService->move($oldProduct, $oldQuantity, 'in', 'sale', $saleItemId);
            $this->stockService->move($newProduct, $newQuantity, 'out', 'sale', $saleItemId);

            return;
        }

        $difference = $newQuantity - $oldQuantity;

        if ($difference > 0) {
            $this->stockService->move($newProduct, $difference, 'out', 'sale', $saleItemId);
        }

        if ($difference < 0) {
            $this->stockService->move($newProduct, abs($difference), 'in', 'sale', $saleItemId);
        }
    }
}
