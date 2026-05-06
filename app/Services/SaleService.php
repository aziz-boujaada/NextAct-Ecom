<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleService
{
    public function __construct(private readonly StockService $stockService) {}

    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::create([
                ...$data,
                'total' => 0,
            ]);

            return $sale->fresh(['client', 'items.product', 'refunds.items.product']);
        });
    }

    public function update(Sale $sale, array $data): Sale
    {
        unset($data['reference'], $data['total'], $data['items']);

        $sale->update($data);

        return $sale->fresh(['client', 'items.product', 'refunds.items.product']);
    }

    public function delete(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale->load(['items.product', 'refunds.items']);

            foreach ($sale->items as $saleItem) {
                $refundedQuantity = $sale->refunds
                    ->flatMap->items
                    ->where('product_id', $saleItem->product_id)
                    ->sum('quantity');

                $remainingQuantity = $saleItem->quantity - $refundedQuantity;

                if ($remainingQuantity > 0) {
                    $this->stockService->move($saleItem->product, $remainingQuantity, 'in', 'sale', $saleItem->id);
                }
            }

            $sale->delete();
        });
    }

    public function refreshTotal(Sale $sale): void
    {
        $itemsTotal = $sale->items()->sum('total');
        $refundsTotal = $sale->refunds()->sum('total');

        $sale->update([
            'total' => max(0, $itemsTotal - $refundsTotal),
        ]);
    }

  
}
