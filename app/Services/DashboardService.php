<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Devis;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function stats(): array
    {
        $totalRefunds = Refund::query()->sum('total');
        $netSales = Sale::query()->sum('total');
        $totalPurchases = Purchase::query()->sum('total');

        return [
            'summary' => [
                'total_refunds' => $this->money($totalRefunds),
                'net_sales' => $this->money($netSales - $totalRefunds),
                'total_purchases' => $this->money($totalPurchases),
                'estimated_profit' => $this->money($netSales - $totalPurchases - $totalRefunds),
            ],
            'counts' => [
                'sales' => Sale::query()->count(),
                'refunds' => Refund::query()->count(),
                'purchases' => Purchase::query()->count(),
                'products' => Product::query()->count(),
                'clients' => Client::query()->count(),
                'suppliers' => Supplier::query()->count(),
                'low_stock_products' => Product::query()->whereColumn('stock', '<=', 'alert_stock')->count(),
            ],
            'today' => [
                'sales' => Sale::query()->whereDate('created_at', today())->count(),
                'sales_total' => $this->money(Sale::query()->whereDate('created_at', today())->sum('total')),
                'refunds' => Refund::query()->whereDate('created_at', today())->count(),
                'refunds_total' => $this->money(Refund::query()->whereDate('created_at', today())->sum('total')),
            ],
            'current_month' => [
                'sales_total' => $this->money(Sale::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total')),
                'refunds_total' => $this->money(Refund::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total')),
                'purchases_total' => $this->money(Purchase::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total')),
            ],
            'sales_by_status' => $this->countByStatus(Sale::class, ['paid', 'unpaid', 'refunded']),
            'devis_by_status' => $this->countByStatus(Devis::class, ['draft', 'sent', 'accepted', 'rejected', 'expired']),
            'purchases_by_status' => $this->countByStatus(Purchase::class, ['pending', 'confirmed']),
            'top_selling_products' => $this->topSellingProducts(),
            'low_stock_products' => $this->lowStockProducts(),
            'recent_sales' => $this->recentSales(),
            'recent_refunds' => $this->recentRefunds(),
        ];
    }

    private function countByStatus(string $model, array $statuses): array
    {
        $counts = $model::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect($statuses)
            ->mapWithKeys(fn (string $status) => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    private function topSellingProducts(): array
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.reference',
                'products.name',
                DB::raw('sum(sale_items.quantity) as quantity_sold'),
                DB::raw('sum(sale_items.total) as sales_total')
            )
            ->groupBy('products.id', 'products.reference', 'products.name')
            ->orderByDesc('quantity_sold')
            ->limit(10)
            ->get()
            ->map(fn ($product) => [
                'id' => $product->id,
                'reference' => $product->reference,
                'name' => $product->name,
                'quantity_sold' => (int) $product->quantity_sold,
                'sales_total' => $this->money($product->sales_total),
            ])
            ->all();
    }

    private function lowStockProducts(): array
    {
        return Product::query()
            ->select('id', 'reference', 'name', 'stock', 'alert_stock')
            ->whereColumn('stock', '<=', 'alert_stock')
            ->orderBy('stock')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function recentSales(): array
    {
        return Sale::query()
            ->with('client:id,name')
            ->latest()
            ->limit(5)
            ->get(['id', 'client_id', 'total', 'status', 'created_at'])
            ->toArray();
    }

    private function recentRefunds(): array
    {
        return Refund::query()
            ->with('sale:id,reference,client_id', 'sale.client:id,name')
            ->latest()
            ->limit(5)
            ->get(['id', 'sale_id', 'total', 'reason', 'created_at'])
            ->toArray();
    }

    private function money(float|int|string|null $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
