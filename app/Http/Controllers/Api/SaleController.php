<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Sale;
use App\Services\SaleService;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $saleService) {}

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'sales' => Sale::with(['client', 'items.product', 'refunds.items.product'])->latest()->get(),
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        $sale = $this->saleService->create($request->validated());

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
            'sale' => $sale->load(['client', 'items.product', 'refunds.items.product']),
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        $sale = $this->saleService->update($sale, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Sale updated successfully',
            'sale' => $sale,
        ]);
    }

    public function destroy(Sale $sale)
    {
        $this->saleService->delete($sale);

        return response()->json([
            'status' => 'success',
            'message' => 'Sale deleted successfully',
        ]);
    }
}
