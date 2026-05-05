<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'purchases' => Purchase::with(['supplier', 'items.product'])->latest()->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request)
    {
        $purchase = Purchase::create($request->validated())->load(['supplier', 'items.product']);

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
