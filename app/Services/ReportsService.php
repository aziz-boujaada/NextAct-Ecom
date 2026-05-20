<?php

namespace App\Services;

use App\Models\Devis;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsService
{
    public function financial(array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        $salesTotal = (float) Sale::query()->whereBetween('created_at', [$from, $to])->sum('total');
        $refundsTotal = (float) Refund::query()->whereBetween('created_at', [$from, $to])->sum('total');
        $refundsTotalAll = (float) Refund::query()->sum('total');
        $refundsToday = (float) Refund::query()->whereDate('created_at', now()->toDateString())->sum('total');
        $refundsMonth = (float) Refund::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total');
        
        $confirmedPurchasesTotal = (float) Purchase::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', 'confirmed')
            ->sum('total');
        $paymentsReceived = (float) Payment::query()
            ->whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount_paid');
        $paymentsReceivedAsOf = (float) Payment::query()
            ->whereDate('paid_at', '<=', $to)
            ->sum('amount_paid');
        $refundsAsOf = (float) Refund::query()
            ->whereDate('created_at', '<=', $to)
            ->sum('total');
        $confirmedPurchasesAsOf = (float) Purchase::query()
            ->whereDate('created_at', '<=', $to)
            ->where('status', 'confirmed')
            ->sum('total');

        $accountsReceivable = $this->accountsReceivable($to);
        $inventoryValue = $this->inventoryValue();
        $pendingPurchaseOrders = (float) Purchase::query()
            ->whereDate('created_at', '<=', $to)
            ->where('status', 'pending')
            ->sum('total');

        $netRevenue = $salesTotal - $refundsTotal;
        $estimatedGrossProfit = $netRevenue - $confirmedPurchasesTotal;
        $netProfit = $paymentsReceived - $refundsTotal - $confirmedPurchasesTotal;

        return [
            'period' => $this->periodPayload($from, $to),
            'refunds_summary' => [
                'refunds_total_all_time' => $this->money($refundsTotalAll),
                'refunds_period' => $this->money($refundsTotal),
                'refunds_today' => $this->money($refundsToday),
                'refunds_month' => $this->money($refundsMonth),
            ],
            'income_statement' => [
                'revenue' => $this->money($salesTotal),
                'refunds' => $this->money($refundsTotal),
                'net_revenue' => $this->money($netRevenue),
                'confirmed_purchases' => $this->money($confirmedPurchasesTotal),
                'estimated_gross_profit' => $this->money($estimatedGrossProfit),
                'net_profit' => $this->money($netProfit),
            ],
            'balance_sheet' => [
                'assets' => [
                    'cash' => $this->money(max(0, $paymentsReceivedAsOf - $refundsAsOf - $confirmedPurchasesAsOf)),
                    'accounts_receivable' => $this->money($accountsReceivable),
                    'inventory' => $this->money($inventoryValue['total']),
                ],
                'liabilities' => [
                    'pending_purchase_orders' => $this->money($pendingPurchaseOrders),
                ],
                'equity' => [
                    'estimated_retained_earnings' => $this->money(
                        max(0, $paymentsReceivedAsOf + $accountsReceivable + $inventoryValue['total'] - $pendingPurchaseOrders)
                    ),
                ],
            ],
            'cash_flow_statement' => [
                'cash_inflows' => [
                    'customer_payments' => $this->money($paymentsReceived),
                ],
                'cash_outflows' => [
                    'refunds' => $this->money($refundsTotal),
                    'confirmed_purchases' => $this->money($confirmedPurchasesTotal),
                ],
                'net_cash_flow' => $this->money($paymentsReceived - $refundsTotal - $confirmedPurchasesTotal),
            ],
            'general_ledger' => [
                [
                    'account' => 'Sales revenue',
                    'debit' => '0.00',
                    'credit' => $this->money($salesTotal),
                ],
                [
                    'account' => 'Refunds',
                    'debit' => $this->money($refundsTotal),
                    'credit' => '0.00',
                ],
                [
                    'account' => 'Customer payments',
                    'debit' => '0.00',
                    'credit' => $this->money($paymentsReceived),
                ],
                [
                    'account' => 'Confirmed purchases',
                    'debit' => $this->money($confirmedPurchasesTotal),
                    'credit' => '0.00',
                ],
            ],
        ];
    }

    public function inventory(array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        $lowStockProducts = Product::query()
            ->select('id', 'reference', 'name', 'stock', 'min_stock', 'alert_stock')
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(10)
            ->get()
            ->map(fn(Product $product) => [
                'id' => $product->id,
                'reference' => $product->reference,
                'name' => $product->name,
                'stock' => (int) $product->stock,
                'min_stock' => (int) $product->min_stock,
                'alert_stock' => (int) $product->alert_stock,
            ])
            ->all();

        $inventoryBreakdown = $this->inventoryValuationBreakdown();

        return [
            'period' => $this->periodPayload($from, $to),
            'stock_levels' => [
                'total_products' => Product::query()->count(),
                'total_units' => (int) Product::query()->sum('stock'),
                'out_of_stock_products' => Product::query()->where('stock', 0)->count(),
                'low_stock_products' => count($lowStockProducts),
                'items' => $lowStockProducts,
            ],
            'inventory_valuation' => [
                'total_value' => $this->money($inventoryBreakdown['total']),
                'items' => $inventoryBreakdown['items'],
            ],
            'turnover_rates' => $this->turnoverRates($from, $to),
            'warehouse_efficiency' => $this->warehouseEfficiency(),
        ];
    }

    public function sales(array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        return [
            'period' => $this->periodPayload($from, $to),
            'sales_volume_by_region' => $this->salesVolumeByRegion($from, $to),
            'product_performance' => $this->productPerformance($from, $to),
            'sales_trends' => $this->salesTrends($from, $to),
        ];
    }

    public function devis(array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        $statusRows = DB::table('devis')
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('COALESCE(SUM(total), 0) as total')
            )
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statusDistribution = [
            'draft' => $this->statusCount($statusRows, 'draft'),
            'sent' => $this->statusCount($statusRows, 'sent'),
            'accepted' => $this->statusCount($statusRows, 'accepted'),
            'rejected' => $this->statusCount($statusRows, 'rejected'),
            'expired' => $this->statusCount($statusRows, 'expired'),
        ];

        $draftTotal = $this->statusTotal($statusRows, 'draft');
        $sentTotal = $this->statusTotal($statusRows, 'sent');
        $acceptedTotal = $this->statusTotal($statusRows, 'accepted');
        $rejectedTotal = $this->statusTotal($statusRows, 'rejected');
        $expiredTotal = $this->statusTotal($statusRows, 'expired');

        $now = now();

        $agingMetrics = DB::table('devis')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', 'sent')
            ->selectRaw("
        SUM(CASE WHEN created_at <= ? THEN 1 ELSE 0 END) as sent_over_7_days,
        SUM(CASE WHEN created_at <= ? THEN 1 ELSE 0 END) as sent_over_30_days
    ", [
                $now->copy()->subDays(7),
                $now->copy()->subDays(30)
            ])
            ->first();

        $totalDevis = array_sum($statusDistribution);
        $acceptedCount = $statusDistribution['accepted'];
        $sentCount = $statusDistribution['sent'];
        $rejectedCount = $statusDistribution['rejected'];
        $expiredCount = $statusDistribution['expired'];

        return [
            'period' => $this->periodPayload($from, $to),
            'total_devis' => $totalDevis,
            'status_distribution' => $statusDistribution,
            'financial_aggregations' => [
                'draft_total' => $this->money($draftTotal),
                'sent_total' => $this->money($sentTotal),
                'accepted_total' => $this->money($acceptedTotal),
                'rejected_total' => $this->money($rejectedTotal),
                'expired_total' => $this->money($expiredTotal),
            ],
            'conversion_metrics' => [
                'acceptance_rate' => $this->rate($acceptedCount, $totalDevis),
            ],
            'pipeline_metrics' => [
                'sent_pending' => $sentCount,
                'sent_total_value' => $this->money($sentTotal),
            ],
            'lost_opportunity_metrics' => [
                'rejected_count' => $rejectedCount,
                'rejected_total' => $this->money($rejectedTotal),
                'expired_count' => $expiredCount,
                'expired_total' => $this->money($expiredTotal),
                'lost_total' => $this->money($rejectedTotal + $expiredTotal),
            ],
            'aging_metrics' => [
                'sent_over_7_days' => (int) ($agingMetrics->sent_over_7_days ?? 0),
                'sent_over_30_days' => (int) ($agingMetrics->sent_over_30_days ?? 0),
            ],
        ];
    }

    public function purchasing(array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        return [
            'period' => $this->periodPayload($from, $to),
            'supplier_performance' => $this->supplierPerformance($from, $to),
            'pending_purchase_orders' => $this->pendingPurchaseOrders($from, $to),
            'expenditure_analysis' => $this->expenditureAnalysis($from, $to),
        ];
    }

    private function resolvePeriod(array $filters): array
    {
        $from = isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : now()->startOfMonth()->startOfDay();
        $to = isset($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : now()->endOfDay();

        return [$from, $to];
    }

    private function periodPayload(Carbon $from, Carbon $to): array
    {
        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    private function money(float|int|string|null $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function statusCount($statusRows, string $status): int
    {
        return (int) ($statusRows->get($status)?->count ?? 0);
    }

    private function statusTotal($statusRows, string $status): float
    {
        return (float) ($statusRows->get($status)?->total ?? 0);
    }

    private function rate(int $numerator, int $denominator): float
    {
        if ($denominator === 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }

    private function accountsReceivable(Carbon $to): float
    {
        return (float) Sale::query()
            ->whereDate('created_at', '<=', $to)
            ->withSum([
                'payments as payments_received' => fn($query) => $query->whereDate('paid_at', '<=', $to),
            ], 'amount_paid')
            ->get(['id', 'total'])
            ->sum(fn(Sale $sale) => max((float) $sale->total - (float) ($sale->payments_received ?? 0), 0));
    }

    private function inventoryValue(): array
    {
        $purchaseTotals = DB::table('purchase_items')
            ->select('product_id', DB::raw('SUM(quantity) as purchased_quantity'), DB::raw('SUM(total) as purchased_total'))
            ->groupBy('product_id');

        $items = DB::table('products')
            ->leftJoinSub($purchaseTotals, 'purchase_totals', 'products.id', '=', 'purchase_totals.product_id')
            ->select(
                'products.id',
                'products.reference',
                'products.name',
                'products.stock',
                'products.price',
                DB::raw('COALESCE(purchase_totals.purchased_quantity, 0) as purchased_quantity'),
                DB::raw('COALESCE(purchase_totals.purchased_total, 0) as purchased_total')
            )
            ->get()
            ->map(function ($product) {
                $averageCost = (float) $product->purchased_quantity > 0
                    ? (float) $product->purchased_total / (float) $product->purchased_quantity
                    : (float) $product->price;

                return [
                    'id' => $product->id,
                    'reference' => $product->reference,
                    'name' => $product->name,
                    'stock' => (int) $product->stock,
                    'average_cost' => $this->money($averageCost),
                    'inventory_value' => $this->money(((int) $product->stock) * $averageCost),
                ];
            })
            ->sortByDesc('inventory_value')
            ->values();

        return [
            'total' => $items->sum(fn(array $item) => (float) $item['inventory_value']),
            'items' => $items->take(10)->all(),
        ];
    }

    private function inventoryValuationBreakdown(): array
    {
        return $this->inventoryValue();
    }

    private function turnoverRates(Carbon $from, Carbon $to): array
    {
        $salesTotals = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->select(
                'sale_items.product_id',
                DB::raw('SUM(sale_items.quantity) as sold_quantity'),
                DB::raw('SUM(sale_items.total) as sales_total')
            )
            ->groupBy('sale_items.product_id');

        return DB::table('products')
            ->leftJoinSub($salesTotals, 'sales_totals', 'products.id', '=', 'sales_totals.product_id')
            ->select(
                'products.id',
                'products.reference',
                'products.name',
                'products.stock',
                DB::raw('COALESCE(sales_totals.sold_quantity, 0) as sold_quantity'),
                DB::raw('COALESCE(sales_totals.sales_total, 0) as sales_total')
            )
            ->get()
            ->map(function ($product) {
                $soldQuantity = (int) $product->sold_quantity;
                $currentStock = (int) $product->stock;
                $averageInventory = max(($currentStock + $soldQuantity) / 2, 1);

                return [
                    'id' => $product->id,
                    'reference' => $product->reference,
                    'name' => $product->name,
                    'sold_quantity' => $soldQuantity,
                    'current_stock' => $currentStock,
                    'turnover_rate' => $this->money($soldQuantity / $averageInventory),
                    'sales_total' => $this->money($product->sales_total),
                ];
            })
            ->sortByDesc('turnover_rate')
            ->take(10)
            ->values()
            ->all();
    }
    private function percentage($value): string
    {
        return round($value * 100, 2) . '%';
    }
    private function warehouseEfficiency(): array
    {
        $totalIn = (int) DB::table('stock_movements')->where('type', 'in')->sum('quantity');
        $totalOut = (int) DB::table('stock_movements')->where('type', 'out')->sum('quantity');
        $totalProducts = Product::query()->count();
        $activeProducts = Product::query()->where('stock', '>', 0)->count();
        $lowStockProducts = Product::query()->whereColumn('stock', '<=', 'min_stock')->count();

        return [
            'movement_throughput' => $this->percentage($totalIn > 0 ? $totalOut / $totalIn : 0),
            'active_sku_ratio' => $this->percentage($totalProducts > 0 ? $activeProducts / $totalProducts : 0),
            'low_stock_ratio' => $this->percentage($totalProducts > 0 ? $lowStockProducts / $totalProducts : 0),
        ];
    }

    private function salesVolumeByRegion(Carbon $from, Carbon $to): array
    {
        return DB::table('sales')
            ->join('clients', 'sales.client_id', '=', 'clients.id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->select(
                DB::raw("COALESCE(NULLIF(clients.address, ''), 'Unknown') as region"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(sales.total) as sales_total')
            )
            ->groupBy('clients.address')
            ->orderByDesc('sales_total')
            ->get()
            ->map(fn($row) => [
                'region' => $row->region,
                'order_count' => (int) $row->order_count,
                'sales_total' => $this->money($row->sales_total),
            ])
            ->all();
    }

    private function productPerformance(Carbon $from, Carbon $to): array
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->select(
                'products.id',
                'products.reference',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as quantity_sold'),
                DB::raw('SUM(sale_items.total) as sales_total')
            )
            ->groupBy('products.id', 'products.reference', 'products.name')
            ->orderByDesc('quantity_sold')
            ->limit(10)
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'reference' => $product->reference,
                'name' => $product->name,
                'quantity_sold' => (int) $product->quantity_sold,
                'sales_total' => $this->money($product->sales_total),
            ])
            ->all();
    }

    private function salesTrends(Carbon $from, Carbon $to): array
    {
        return DB::table('sales')
            ->whereBetween('created_at', [$from, $to])
            ->select(
                DB::raw('DATE(created_at) as report_date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as sales_total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('report_date')
            ->get()
            ->map(fn($row) => [
                'date' => $row->report_date,
                'order_count' => (int) $row->order_count,
                'sales_total' => $this->money($row->sales_total),
            ])
            ->all();
    }

    private function supplierPerformance(Carbon $from, Carbon $to): array
    {
        return DB::table('purchases')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->whereBetween('purchases.created_at', [$from, $to])
            ->select(
                'suppliers.id',
                'suppliers.name',
                DB::raw('COUNT(*) as purchase_count'),
                DB::raw("SUM(CASE WHEN purchases.status = 'confirmed' THEN purchases.total ELSE 0 END) as confirmed_spend"),
                DB::raw("SUM(CASE WHEN purchases.status = 'pending' THEN purchases.total ELSE 0 END) as pending_spend"),
                DB::raw('AVG(purchases.total) as average_order_value')
            )
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('confirmed_spend')
            ->get()
            ->map(fn($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'purchase_count' => (int) $supplier->purchase_count,
                'confirmed_spend' => $this->money($supplier->confirmed_spend),
                'pending_spend' => $this->money($supplier->pending_spend),
                'average_order_value' => $this->money($supplier->average_order_value),
            ])
            ->all();
    }

    private function pendingPurchaseOrders(Carbon $from, Carbon $to): array
    {
        return Purchase::query()
            ->with('supplier:id,name')
            ->where('status', 'pending')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->limit(10)
            ->get(['id', 'supplier_id', 'total', 'status', 'created_at'])
            ->map(fn(Purchase $purchase) => [
                'id' => $purchase->id,
                'supplier' => $purchase->supplier?->name,
                'total' => $this->money($purchase->total),
                'status' => $purchase->status,
                'created_at' => optional($purchase->created_at)?->toDateTimeString(),
            ])
            ->all();
    }

    private function expenditureAnalysis(Carbon $from, Carbon $to): array
    {
        $purchaseItems = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('purchases.created_at', [$from, $to])
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(purchase_items.quantity) as quantity'),
                DB::raw('SUM(purchase_items.total) as total_spend')
            )
            ->groupBy('categories.id', 'categories.name', 'products.id', 'products.name')
            ->get();

        return [
            'total_spend' => $this->money($purchaseItems->sum('total_spend')),
            'by_category' => $purchaseItems
                ->groupBy('category_id')
                ->map(fn($categoryItems) => [
                    'category' => $categoryItems->first()->category_name,
                    'total_spend' => $this->money($categoryItems->sum('total_spend')),
                ])
                ->values()
                ->all(),
            'top_products' => $purchaseItems
                ->sortByDesc('total_spend')
                ->take(10)
                ->map(fn($item) => [
                    'id' => $item->product_id,
                    'name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'total_spend' => $this->money($item->total_spend),
                ])
                ->values()
                ->all(),
        ];
    }
}
