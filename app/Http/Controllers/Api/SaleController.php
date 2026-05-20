<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Sale;
use App\Services\SaleService;
use App\Services\SaleItemService;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $saleService, private readonly SaleItemService $saleItemService)
    {
        $this->middleware('permissions:view_sales')->only(['index', 'show']);

        $this->middleware('permissions:create_sales')->only(['store']);

        $this->middleware('permissions:edit_sales')->only(['update']);

        $this->middleware('permissions:delete_sales')->only(['destroy']);
    }

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'sales' => Sale::with(['client', 'items.product', 'refunds.items.product'])->latest()->get(),
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        $validated = $request->validated();
        $items = $validated['items'] ?? [];

        $saleData = [
            'client_id' => $validated['client_id'],
            'status' => $validated['status'] ?? 'unpaid',
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount_amount' => $validated['discount_amount'] ?? 0,
        ];

        $sale = DB::transaction(function () use ($saleData, $items) {
            $sale = $this->saleService->create($saleData);

            if (!empty($items)) {
                foreach ($items as $itemData) {
                    $this->saleItemService->create(array_merge($itemData, ['sale_id' => $sale->id]));
                }
            }

            return $sale->fresh(['client', 'items.product', 'refunds.items.product']);
        });

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
