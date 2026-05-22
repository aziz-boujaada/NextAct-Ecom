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
        private readonly SaleService $saleService,
        private readonly AlertsService $alertsService
    ) {}

    public function create(array $data): Refund
    {
        return DB::transaction(function () use ($data) {

            $sale = Sale::with([
                'items.product',
                'refunds.items'
            ])
                ->lockForUpdate()
                ->findOrFail($data['sale_id']);

            $items = $data['items'];
            unset($data['items']);

            $refund = Refund::create([
                ...$data,
                'total' => 0,
            ]);

            $refundTotal = 0;
            $originalTotal = $sale->subtotal;

            foreach ($items as $item) {

                $saleItem = $sale->items->firstWhere(
                    'product_id',
                    $item['product_id']
                );

                if (!$saleItem) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'The selected product was not sold in this sale.'
                        ],
                    ]);
                }

                $refundQty = (int) $item['quantity'];


                $availableToRefund =
                    $saleItem->quantity -
                    $saleItem->refund_quantity;

                if ($refundQty > $availableToRefund) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "The refunded quantity for product {$item['product_id']} exceeds the available quantity."
                        ],
                    ]);
                }

                // Proportional refund calculation
                $itemSubtotal =
                    $saleItem->price *
                    $refundQty;

                $ratio = $sale->subtotal > 0
                    ? $itemSubtotal / $originalTotal
                    : 0;

                $discountRefund = $sale->discount_amount * $ratio;

                $taxableRefund = $itemSubtotal - $discountRefund;
                $taxRefund = $taxableRefund * ($sale->tax_rate / 100);

                $effectiveRefundTotal = round(
                    $itemSubtotal - $discountRefund + $taxRefund,
                    2
                );

                $effectivePrice = $refundQty > 0
                    ? round(
                        $effectiveRefundTotal / $refundQty,
                        2
                    )
                    : 0;


                $refundItem = RefundItem::create([
                    'refund_id' => $refund->id,
                    'product_id' => $saleItem->product_id,
                    'price' => $effectivePrice,
                    'quantity' => $refundQty,
                    'total' => $effectiveRefundTotal,
                ]);

                // Update sale item refund values
                $saleItem->refund_quantity += $refundQty;
                $saleItem->refund_total += $effectiveRefundTotal;

                // Refund status
                if ($saleItem->refund_quantity == 0) {
                    $saleItem->refund_status = 'none';
                } elseif (
                    $saleItem->refund_quantity <
                    $saleItem->quantity
                ) {
                    $saleItem->refund_status = 'partial';
                } else {
                    $saleItem->refund_status = 'refunded';
                }

                $saleItem->save();

                // Accumulate total
                $refundTotal += $effectiveRefundTotal;

                // Stock movement
                $this->stockService->move(
                    $saleItem->product,
                    $refundItem->quantity,
                    'in',
                    'refund',
                    $refundItem->id
                );

                $this->alertsService->handle(
                    $saleItem->product
                );
            }

            // Update refund total
            $refund->update([
                'total' => round($refundTotal, 2),
            ]);

            // Update sale total ONCE

            $taxableAmount =$sale->subtotal - $sale->discount_amount;

            $originalFinalTotal = $taxableAmount + ($taxableAmount * ($sale->tax_rate / 100));

            $sale->total = max(0,round($originalFinalTotal- $sale->refunds()->sum('total'), 2 ));

            $sale->save();

            // Update sale refund status
            $this->updateSaleStatus($sale);

            $sale->refresh();

            return $refund->fresh([
                'sale.client',
                'items.product'
            ]);
        });
    }

    /**
     * Update sale status based on item-level refund states.
     */
    private function updateSaleStatus(Sale $sale): void
    {
        $sale->load('items');

        $totalItems = $sale->items->count();
        $fullyRefundedItems = $sale->items
            ->where('refund_status', 'refunded')
            ->count();

        if ($fullyRefundedItems === $totalItems && $totalItems > 0) {
            $sale->status = 'refunded';
        } elseif ($fullyRefundedItems > 0 || $sale->items->contains('refund_status', 'partial')) {
            $sale->status = 'partial_refunded';
        }

        $sale->save();
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
                // Reverse previous refund amounts from sale_items and sale total
                foreach ($refund->items as $existingItem) {
                    $saleItem = $sale->items->firstWhere('product_id', $existingItem->product_id);

                    if ($saleItem) {
                        // Reverse the refund_quantity and refund_total
                        $saleItem->refund_quantity -= $existingItem->quantity;
                        $saleItem->refund_total -= $existingItem->total;

                        // Reset refund status
                        if ($saleItem->refund_quantity == 0) {
                            $saleItem->refund_status = 'none';
                        } elseif ($saleItem->refund_quantity < $saleItem->quantity) {
                            $saleItem->refund_status = 'partial';
                        } else {
                            $saleItem->refund_status = 'refunded';
                        }

                        $saleItem->save();

                        // Restore sale total
                        $restoreAmount = round((float) $sale->total + $existingItem->total, 2);
                        $sale->update(['total' => $restoreAmount]);
                    }

                    $this->stockService->move($existingItem->product, $existingItem->quantity, 'out', 'refund', $existingItem->id);
                }

                $refund->items()->delete();

                // Apply new refunds with proportional pricing
                foreach ($data['items'] as $item) {
                    $saleItem = $sale->items->firstWhere('product_id', $item['product_id']);

                    if (! $saleItem) {
                        throw ValidationException::withMessages([
                            'items' => ['The selected product was not sold in this sale.'],
                        ]);
                    }

                    $refundQty = (int) $item['quantity'];

                    // Validate available refundable quantity
                    $availableToRefund = $saleItem->quantity - $saleItem->refund_quantity;

                    if ($refundQty > $availableToRefund) {
                        throw ValidationException::withMessages([
                            'items' => ["The refunded quantity for product {$item['product_id']} exceeds the available quantity."],
                        ]);
                    }

                    // Calculate proportional refund pricing
                    $itemSubtotal = $saleItem->price * $refundQty;

                    $ratio = $sale->subtotal > 0
                        ? $itemSubtotal / $sale->subtotal
                        : 0;

                    $effectiveRefundTotal = round($sale->total * $ratio, 2);

                    $effectivePrice = $refundQty > 0
                        ? round($effectiveRefundTotal / $refundQty, 2)
                        : 0;

                    // Create new refund item
                    $refundItem = RefundItem::create([
                        'refund_id' => $refund->id,
                        'product_id' => $saleItem->product_id,
                        'price' => $effectivePrice,
                        'quantity' => $refundQty,
                        'total' => $effectiveRefundTotal,
                    ]);

                    // Update sale_item refund fields
                    $saleItem->refund_quantity += $refundQty;
                    $saleItem->refund_total += $effectiveRefundTotal;

                    // Determine refund status for this item
                    if ($saleItem->refund_quantity == 0) {
                        $saleItem->refund_status = 'none';
                    } elseif ($saleItem->refund_quantity < $saleItem->quantity) {
                        $saleItem->refund_status = 'partial';
                    } else {
                        $saleItem->refund_status = 'refunded';
                    }

                    $saleItem->save();

                    // Update sale total safely - CRITICAL: do this immediately after each refund
                    $sale->total = (float) max(0, round((float) $sale->total - $effectiveRefundTotal, 2));
                    $sale->save();

                    $this->stockService->move($saleItem->product, $refundItem->quantity, 'in', 'refund', $refundItem->id);
                    $this->alertsService->handle($saleItem->product);
                }

                // Update sale status based on item-level refund states
                $this->updateSaleStatus($sale);
            }

            $refund->update([
                'total' => $refund->items()->sum('total'),
            ]);

            // Reload sale to reflect final totals after all refunds applied
            $sale->refresh();

            return $refund->fresh(['sale.client', 'items.product']);
        });
    }

    public function delete(Refund $refund): void
    {
        DB::transaction(function () use ($refund) {
            $refund->load(['sale.items', 'items.product']);
            $sale = $refund->sale;

            foreach ($refund->items as $refundItem) {
                // Find corresponding sale_item
                $saleItem = $sale->items->firstWhere('product_id', $refundItem->product_id);

                if ($saleItem) {
                    // Reverse the refund_quantity and refund_total
                    $saleItem->refund_quantity -= $refundItem->quantity;
                    $saleItem->refund_total -= $refundItem->total;

                    // Ensure values don't go below zero
                    $saleItem->refund_quantity = max(0, $saleItem->refund_quantity);
                    $saleItem->refund_total = max(0, $saleItem->refund_total);

                    // Update refund status
                    if ($saleItem->refund_quantity == 0) {
                        $saleItem->refund_status = 'none';
                    } elseif ($saleItem->refund_quantity < $saleItem->quantity) {
                        $saleItem->refund_status = 'partial';
                    } else {
                        $saleItem->refund_status = 'refunded';
                    }

                    $saleItem->save();

                    // Restore sale total
                    $restoreAmount = round((float) $sale->total + $refundItem->total, 2);
                    $sale->update(['total' => $restoreAmount]);
                }

                $this->stockService->move($refundItem->product, $refundItem->quantity, 'out', 'refund', $refundItem->id);
            }

            // Update sale status based on item-level refund states
            $this->updateSaleStatus($sale);

            $refund->delete();
            // Reload sale to reflect the final refund state
            $sale->refresh();
        });
    }
}
