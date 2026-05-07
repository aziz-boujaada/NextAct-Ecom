<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseItemRequest;
use App\Http\Requests\UpdatePurchaseItemRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Services\PurchaseItemService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseItemController extends Controller
{

private PurchaseItemService $purchaseItemService;
public function __construct(PurchaseItemService $purchaseItemService)
{
  $this->purchaseItemService = $purchaseItemService ;
}
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'purchase_items' => PurchaseItem::with(['purchase.supplier', 'product'])->latest()->get(),
        ]);
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
            $product = $this->purchaseItemService->productForPurchase($purchaseId, $productId);

            $data['price'] = $product->price;
            $price = $data['price'];
            $quantity = $data['quantity'] ?? $purchaseItem->quantity;
            $data['total'] = $price * $quantity;

            $purchaseItem->update($data);
            $this->syncUpdatedItemStock($oldProduct, $product, $oldQuantity, $quantity, $purchaseItem->id);
            $purchaseItem = $purchaseItem->fresh(['purchase.supplier', 'product']);

            $this->purchaseItemService->refreshPurchaseTotal($oldPurchase);

            if ($oldPurchase->isNot($purchaseItem->purchase)) {
                $this->purchaseItemService->refreshPurchaseTotal($purchaseItem->purchase);
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
            $this->purchaseItemService->applyStockMovement($product, $quantity, 'out', $referenceId);
            $this->purchaseItemService->refreshPurchaseTotal($purchase);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase item deleted successfully',
        ]);
    }

  



    private function syncUpdatedItemStock(
        Product $oldProduct,
        Product $newProduct,
        int $oldQuantity,
        int $newQuantity,
        int $purchaseItemId
    ): void {
        if ($oldProduct->isNot($newProduct)) {
            $this->purchaseItemService->applyStockMovement($oldProduct, $oldQuantity, 'out', $purchaseItemId);
            $this->purchaseItemService->applyStockMovement($newProduct, $newQuantity, 'in', $purchaseItemId);

            return;
        }

        $difference = $newQuantity - $oldQuantity;

        if ($difference > 0) {
            $this->purchaseItemService->applyStockMovement($newProduct, $difference, 'in', $purchaseItemId);
        }

        if ($difference < 0) {
            $this->purchaseItemService->applyStockMovement($newProduct, abs($difference), 'out', $purchaseItemId);
        }
    }

    
}
