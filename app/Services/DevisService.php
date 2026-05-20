<?php

namespace App\Services;

use App\Models\Devis;
use App\Models\Sale;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DevisService
{
    public function __construct(
        private readonly DevisItemService $devisItemService,
        private readonly SaleService $saleService,
        private readonly SaleItemService $saleItemService,
    ) {}

    public function create(array $data): Devis
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $devis = Devis::create([
                ...$data,
                'reference' => $this->generateReference(),
                'status' => $data['status'] ?? 'draft',
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'subtotal' => 0,
                'total' => 0,
            ]);

            if (!empty($items)) {
                $this->syncItems($devis, $items);
            }

            return $devis->fresh(['client', 'createdBy', 'items.product']);
        });
    }

    public function update(Devis $devis, array $data): Devis
    {
        return DB::transaction(function () use ($devis, $data) {
            $items = $data['items'] ?? null;
            unset($data['items'], $data['reference'], $data['subtotal'], $data['total'], $data['created_by']);

            $devis->update($data);

            if (is_array($items)) {
                $this->syncItems($devis, $items);
            } else {
                $this->refreshTotals($devis);
            }

            return $devis->fresh(['client', 'createdBy', 'items.product']);
        });
    }

    public function delete(Devis $devis): void
    {
        logger()->info('Before delete', [
            'id' => $devis->id,
            'exists' => $devis->exists,
        ]);

        $result = $devis->delete();

        logger()->info('After delete', [
            'result' => $result,
        ]);
    }

    public function send(Devis $devis): Devis
    {
        return $this->transition($devis, 'sent', ['draft', 'rejected']);
    }

    public function reject(Devis $devis): Devis
    {
        return $this->transition($devis, 'rejected', ['draft', 'sent']);
    }

    public function expire(Devis $devis): Devis
    {
        return $this->transition($devis, 'expired', ['draft', 'sent']);
    }

    public function autoExpired(int $id){
      

         $devis = Devis::findOrFail($id);

         if($devis->expires_at && $devis->expires_at->isPast()){
            $devis->update([
                'status' => 'expired'
            ]);
         }
    }

    public function acceptAndConvert(Devis $devis): array
    {
        return DB::transaction(function () use ($devis) {
            $devis = $this->transition($devis, 'accepted', ['draft', 'sent']);

            $sale = $this->saleService->create([
                'client_id' => $devis->client_id,
                'status' => 'unpaid',
                'tax_rate' => $devis->tax,
                'discount_amount' => $devis->discount,
            ]);

            $devis->loadMissing(['items.product']);

            foreach ($devis->items as $item) {
                $this->saleItemService->create([
                    'sale_id' => $sale->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            return [
                'devis' => $devis->fresh(['client', 'createdBy', 'items.product']),
                'sale' => $sale->fresh(['client', 'items.product', 'refunds.items.product']),
            ];
        });
    }

    public function refreshTotals(Devis $devis): void
    {
        $subtotal = (float) $devis->items()->sum('total');
        $discount = (float) $devis->discount;
        $taxRate = (float) $devis->tax;

        $net = max(0, $subtotal - $discount);
        $tax = $net * $taxRate / 100;
        $total = $net + $tax;

        $devis->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    public function syncItems(Devis $devis, array $items): void
    {
        $devis->items()->delete();

        foreach ($items as $item) {
            $this->devisItemService->create($devis, $item);
        }

        $this->refreshTotals($devis);
    }

    public function generateReference(): string
    {
        $year = now()->year;
        $count = Devis::query()->whereYear('created_at', $year)->count() + 1;

        return sprintf('DEV-%d-%04d', $year, $count);
    }

    private function transition(Devis $devis, string $targetStatus, array $allowedCurrentStatuses): Devis
    {
        if (!in_array($devis->status, $allowedCurrentStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => ["The devis cannot transition from {$devis->status} to {$targetStatus}."],
            ]);
        }

        $devis->update(['status' => $targetStatus]);

        return $devis->fresh(['client', 'createdBy', 'items.product']);
    }
}
