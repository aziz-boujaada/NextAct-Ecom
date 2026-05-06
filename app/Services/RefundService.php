<?php

namespace App\Services;

use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly SaleService $saleService
    ) {}

    public function create(array $data): Refund
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::with(['items.product', 'refunds.items'])->lockForUpdate()->findOrFail($data['sale_id']);
            $items = $data['items'];

            unset($data['items']);

            $refund = Refund::create([
                ...$data,
                'total' => 0,
            ]);

            foreach ($items as $item) {
                $saleItem = $sale->items->firstWhere('product_id', $item['product_id']);

                if (! $saleItem) {
                    throw ValidationException::withMessages([
                        'items' => ['The selected product was not sold in this sale.'],
                    ]);
                }

                $alreadyRefunded = $sale->refunds
                    ->flatMap->items
                    ->where('product_id', $item['product_id'])
                    ->sum('quantity');

                $availableToRefund = $saleItem->quantity - $alreadyRefunded;

                if ($item['quantity'] > $availableToRefund) {
                    throw ValidationException::withMessages([
                        'items' => ["The refunded quantity for product {$item['product_id']} exceeds the sold quantity."],
                    ]);
                }

                $refundItem = RefundItem::create([
                    'refund_id' => $refund->id,
                    'product_id' => $saleItem->product_id,
                    'price' => $saleItem->price,
                    'quantity' => $item['quantity'],
                    'total' => $saleItem->price * $item['quantity'],
                ]);

                $this->stockService->move($saleItem->product, $refundItem->quantity, 'in', 'refund', $refundItem->id);
            }

            $refund->update([
                'total' => $refund->items()->sum('total'),
            ]);

            $this->saleService->refreshTotal($sale);

            return $refund->fresh(['sale.client', 'items.product']);
        });
    }

    public function delete(Refund $refund): void
    {
        DB::transaction(function () use ($refund) {
            $refund->load(['sale', 'items.product']);
            $sale = $refund->sale;

            foreach ($refund->items as $refundItem) {
                $this->stockService->move($refundItem->product, $refundItem->quantity, 'out', 'refund', $refundItem->id);
            }

            $refund->delete();
            $this->saleService->refreshTotal($sale);
        });
    }
}
