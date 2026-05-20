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
            $reference = 'SALE-' . Str::random(6);

            $sale = Sale::create([
                ...$data,
                'reference' => $reference,
                'subtotal' => 0,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'tax_amount' => 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'total' => 0,
            ]);

            return $sale->fresh(['client', 'items.product', 'refunds.items.product']);
        });
    }

    public function update(Sale $sale, array $data): Sale
    {
        unset($data['reference'], $data['subtotal'], $data['total'], $data['items']);

        $sale->update($data);

        $this->refreshTotals($sale);

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

    public function refreshTotals(Sale $sale): void
    {
        $subtotal = (float) $sale->items()->sum('total');
        $discountAmount = (float) $sale->discount_amount;
        $taxRate = (float) $sale->tax_rate;

        $net = max(0, $subtotal - $discountAmount);
        $taxAmount = $net * $taxRate / 100;
        $total = $net + $taxAmount;

        $sale->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

}
