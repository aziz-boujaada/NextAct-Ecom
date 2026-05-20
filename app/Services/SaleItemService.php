<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SaleItemService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly SaleService $saleService,
        private AlertsService $alertsService
    ) {}

    public function create(array $data): SaleItem
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            $data['price'] = $data['price'] ?? $product->price;
            $data['total'] = $data['price'] * $data['quantity'];

            $saleItem = SaleItem::create($data);

            $this->stockService->move($product, $saleItem->quantity, 'out', 'sale', $saleItem->id);
            $this->alertsService->stockAlert($product->fresh());
            $this->saleService->refreshTotals($saleItem->sale);

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

            $this->saleService->refreshTotals($oldSale);

            if ($oldSale->isNot($saleItem->sale)) {
                $this->saleService->refreshTotals($saleItem->sale);
            }

            $this->alertsService->stockAlert($oldProduct->fresh());
            $this->alertsService->stockAlert($product->fresh());

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
            $this->saleService->refreshTotals($sale);
        });
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
