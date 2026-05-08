<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Sale;
use App\Services\SaleService;
use App\Services\SaleItemService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $saleService, private readonly SaleItemService $saleItemService) {}

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
        ];

        $reference = 'SALE-' . Str::random(6);

        $sale = DB::transaction(function () use ($saleData, $items, $reference) {
            $sale = Sale::create([
                ...$saleData,
                'total' => 0,
                'reference' => $reference,
            ]);

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
