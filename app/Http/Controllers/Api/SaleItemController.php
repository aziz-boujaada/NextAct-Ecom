<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleItemRequest;
use App\Http\Requests\UpdateSaleItemRequest;
use App\Models\SaleItem;
use App\Services\SaleItemService;

class SaleItemController extends Controller
{
    public function __construct(private readonly SaleItemService $saleItemService) {}

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'sale_items' => SaleItem::with(['sale.client', 'product'])->latest('id')->get(),
        ]);
    }

    public function store(StoreSaleItemRequest $request)
    {
        $saleItem = $this->saleItemService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Sale item created successfully',
            'sale_item' => $saleItem,
        ], 201);
    }

    public function show(SaleItem $saleItem)
    {
        return response()->json([
            'status' => 'success',
            'sale_item' => $saleItem->load(['sale.client', 'product']),
        ]);
    }

    public function update(UpdateSaleItemRequest $request, SaleItem $saleItem)
    {
        $saleItem = $this->saleItemService->update($saleItem, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Sale item updated successfully',
            'sale_item' => $saleItem,
        ]);
    }

    public function destroy(SaleItem $saleItem)
    {
        $this->saleItemService->delete($saleItem);

        return response()->json([
            'status' => 'success',
            'message' => 'Sale item deleted successfully',
        ]);
    }
}
