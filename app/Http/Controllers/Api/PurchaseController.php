<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\PurchaseItemService;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{

private PurchaseItemService $purchaseItemService ;
public function __construct(PurchaseItemService $purchaseItemService)
{
    $this->purchaseItemService = $purchaseItemService ;
}
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'purchases' => Purchase::with(['supplier', 'items.product'])->latest()->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request)
    {
        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        
        
        $purchaseData = [
            'supplier_id' => $validated['supplier_id'],
            'status' => $validated['status'] ?? 'pending',
        ];

        $purchase = DB::transaction(function () use ($purchaseData, $items) {
            
            $purchase = Purchase::create($purchaseData);

            
            if (!empty($items)) {
                foreach ($items as $itemData) {
                    $product = $this->purchaseItemService->productForPurchase($purchase->id, $itemData['product_id']);

                    $purchaseItem = PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $product->price,
                        'total' => $product->price * $itemData['quantity'],
                    ]);

                    $this->purchaseItemService->applyStockMovement($product, $purchaseItem->quantity, 'in', $purchaseItem->id);
                }
                
            
                $this->purchaseItemService->refreshPurchaseTotal($purchase);
            }

            return $purchase->load(['supplier', 'items.product']);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase created successfully',
            'purchase' => $purchase,
        ], 201);
    }

    public function show(Purchase $purchase)
    {
        return response()->json([
            'status' => 'success',
            'purchase' => $purchase->load(['supplier', 'items.product']),
        ]);
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        $purchase->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase updated successfully',
            'purchase' => $purchase->fresh()->load(['supplier', 'items.product']),
        ]);
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase deleted successfully',
        ]);
    }
}
