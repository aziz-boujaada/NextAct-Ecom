<?php

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an admin can view dashboard statistics', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $headers = [
        'Authorization' => 'Bearer '.auth('api')->login($admin),
        'Accept' => 'application/json',
    ];

    $client = Client::create(['name' => 'Dashboard Client']);
    $supplier = Supplier::create(['name' => 'Dashboard Supplier']);
    $category = Category::create(['name' => 'Dashboard Category']);
    $product = Product::create([
        'reference' => 'DASH-001',
        'name' => 'Dashboard Product',
        'price' => 100,
        'stock' => 1,
        'min_stock' => 2,
        'category_id' => $category->id,
        'supplier_id' => $supplier->id,
    ]);

    $sale = Sale::create([
        'reference' => 'SALE-DASH-001',
        'client_id' => $client->id,
        'total' => 80,
        'status' => 'paid',
    ]);
    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'price' => 100,
        'quantity' => 1,
        'total' => 100,
    ]);
    Refund::create([
        'sale_id' => $sale->id,
        'total' => 20,
        'reason' => 'Damaged item',
    ]);
    Purchase::create([
        'supplier_id' => $supplier->id,
        'total' => 40,
        'status' => 'confirmed',
    ]);

    $this
        ->withHeaders($headers)
        ->getJson('/api/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.summary.gross_sales', '100.00')
        ->assertJsonPath('data.summary.total_refunds', '20.00')
        ->assertJsonPath('data.summary.net_sales', '80.00')
        ->assertJsonPath('data.summary.total_purchases', '40.00')
        ->assertJsonPath('data.summary.estimated_profit', '40.00')
        ->assertJsonPath('data.counts.sales', 1)
        ->assertJsonPath('data.counts.refunds', 1)
        ->assertJsonPath('data.counts.low_stock_products', 1)
        ->assertJsonPath('data.sales_by_status.paid', 1)
        ->assertJsonPath('data.purchases_by_status.confirmed', 1)
        ->assertJsonPath('data.top_selling_products.0.name', 'Dashboard Product')
        ->assertJsonPath('data.low_stock_products.0.reference', 'DASH-001')
        ->assertJsonPath('data.recent_sales.0.reference', 'SALE-DASH-001')
        ->assertJsonPath('data.recent_refunds.0.reason', 'Damaged item');
});

test('dashboard statistics are restricted to admins', function () {
    $employee = User::factory()->create(['role' => User::ROLE_EMPLOYEE]);

    $this
        ->withHeaders([
            'Authorization' => 'Bearer '.auth('api')->login($employee),
            'Accept' => 'application/json',
        ])
        ->getJson('/api/admin/dashboard')
        ->assertForbidden();

    $this->getJson('/api/admin/dashboard')->assertUnauthorized();
});
