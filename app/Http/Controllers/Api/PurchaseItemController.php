<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseItemRequest;
use App\Http\Requests\UpdatePurchaseItemRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseItemController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'purchase_items' => PurchaseItem::with(['purchase.supplier', 'product'])->latest()->get(),
        ]);
    }

    public function store(StorePurchaseItemRequest $request)
    {
        $purchaseItem = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $product = $this->productForPurchase($data['purchase_id'], $data['product_id']);

            $data['price'] = $product->price;
            $data['total'] = $data['price'] * $data['quantity'];

            $purchaseItem = PurchaseItem::create($data)->load(['purchase.supplier', 'product']);
            $this->applyStockMovement($product, $purchaseItem->quantity, 'in', $purchaseItem->id);
            $this->refreshPurchaseTotal($purchaseItem->purchase);

            return $purchaseItem;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase item created successfully',
            'purchase_item' => $purchaseItem->fresh()->load(['purchase.supplier', 'product']),
        ], 201);
    }

    public function show(PurchaseItem $purchaseItem)
    {
        return response()->json([
            'status' => 'success',
            'purchase_item' => $purchaseItem->load(['purchase.supplier', 'product']),
        ]);
    }

    public function update(UpdatePurchaseItemRequest $request, PurchaseItem $purchaseItem)
    {
        $purchaseItem = DB::transaction(function () use ($request, $purchaseItem) {
            $oldPurchase = $purchaseItem->purchase;
            $oldProduct = $purchaseItem->product;
            $oldQuantity = $purchaseItem->quantity;
            $data = $request->validated();

            $purchaseId = $data['purchase_id'] ?? $purchaseItem->purchase_id;
            $productId = $data['product_id'] ?? $purchaseItem->product_id;
            $product = $this->productForPurchase($purchaseId, $productId);

            $data['price'] = $product->price;
            $price = $data['price'];
            $quantity = $data['quantity'] ?? $purchaseItem->quantity;
            $data['total'] = $price * $quantity;

            $purchaseItem->update($data);
            $this->syncUpdatedItemStock($oldProduct, $product, $oldQuantity, $quantity, $purchaseItem->id);
            $purchaseItem = $purchaseItem->fresh(['purchase.supplier', 'product']);

            $this->refreshPurchaseTotal($oldPurchase);

            if ($oldPurchase->isNot($purchaseItem->purchase)) {
                $this->refreshPurchaseTotal($purchaseItem->purchase);
            }

            return $purchaseItem;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase item updated successfully',
            'purchase_item' => $purchaseItem,
        ]);
    }

    public function destroy(PurchaseItem $purchaseItem)
    {
        DB::transaction(function () use ($purchaseItem) {
            $purchase = $purchaseItem->purchase;
            $product = $purchaseItem->product;
            $quantity = $purchaseItem->quantity;
            $referenceId = $purchaseItem->id;

            $purchaseItem->delete();
            $this->applyStockMovement($product, $quantity, 'out', $referenceId);
            $this->refreshPurchaseTotal($purchase);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase item deleted successfully',
        ]);
    }

    private function refreshPurchaseTotal(Purchase $purchase): void
    {
        $purchase->update([
            'total' => $purchase->items()->sum('total'),
        ]);
    }

    private function productForPurchase(int $purchaseId, int $productId): Product
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

    private function syncUpdatedItemStock(
        Product $oldProduct,
        Product $newProduct,
        int $oldQuantity,
        int $newQuantity,
        int $purchaseItemId
    ): void {
        if ($oldProduct->isNot($newProduct)) {
            $this->applyStockMovement($oldProduct, $oldQuantity, 'out', $purchaseItemId);
            $this->applyStockMovement($newProduct, $newQuantity, 'in', $purchaseItemId);

            return;
        }

        $difference = $newQuantity - $oldQuantity;

        if ($difference > 0) {
            $this->applyStockMovement($newProduct, $difference, 'in', $purchaseItemId);
        }

        if ($difference < 0) {
            $this->applyStockMovement($newProduct, abs($difference), 'out', $purchaseItemId);
        }
    }

    private function applyStockMovement(Product $product, int $quantity, string $type, int $purchaseItemId): void
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
}
