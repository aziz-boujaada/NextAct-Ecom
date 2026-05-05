<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Sale;
use App\Services\SaleItemService;

class SaleController extends Controller
{
    public function __construct(private readonly SaleItemService $saleItemService) {}

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'sales' => Sale::with(['client', 'items.product'])->latest()->get(),
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        $data = $request->validated();
        $data['total'] = 0;

        $sale = Sale::create($data)->load(['client', 'items.product']);

        return response()->json([
            'status' => 'success',
            'message' => 'Sale created successfully',
            'sale' => $sale,
        ], 201);
    }

    public function show(Sale $sale)
    {
        return response()->json([
            'status' => 'success',
            'sale' => $sale->load(['client', 'items.product']),
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        $sale->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Sale updated successfully',
            'sale' => $sale->fresh()->load(['client', 'items.product']),
        ]);
    }

    public function destroy(Sale $sale)
    {
        $sale->load('items.product');

        foreach ($sale->items as $saleItem) {
            $this->saleItemService->delete($saleItem);
        }

        $sale->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sale deleted successfully',
        ]);
    }
}
