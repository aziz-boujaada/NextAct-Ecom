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
        private readonly SaleService $saleService ,
        private readonly AlertsService $alertsService
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
                $this->alertsService->handle($saleItem->product);
            }

            $refund->update([
                'total' => $refund->items()->sum('total'),
            ]);

            $this->saleService->refreshTotal($sale);

            return $refund->fresh(['sale.client', 'items.product']);
        });
    }

    public function update(Refund $refund, array $data): Refund
    {
        return DB::transaction(function () use ($refund, $data) {
            $refund = Refund::with(['items.product', 'sale.items.product'])->lockForUpdate()->findOrFail($refund->id);
            $sale = $refund->sale;

            if (array_key_exists('reason', $data)) {
                $refund->update([
                    'reason' => $data['reason'],
                ]);
            }

            if (array_key_exists('items', $data)) {
                foreach ($refund->items as $existingItem) {
                    $this->stockService->move($existingItem->product, $existingItem->quantity, 'out', 'refund', $existingItem->id);
                }

                $refund->items()->delete();

                foreach ($data['items'] as $item) {
                    $saleItem = $sale->items->firstWhere('product_id', $item['product_id']);

                    if (! $saleItem) {
                        throw ValidationException::withMessages([
                            'items' => ['The selected product was not sold in this sale.'],
                        ]);
                    }

                    $alreadyRefunded = RefundItem::query()
                        ->where('product_id', $item['product_id'])
                        ->where('refund_id', '!=', $refund->id)
                        ->whereHas('refund', function ($query) use ($sale) {
                            $query->where('sale_id', $sale->id);
                        })
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
