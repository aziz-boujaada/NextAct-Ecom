<?php

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();

    $this->headers = [
        'Authorization' => 'Bearer '.auth('api')->login($user),
        'Accept' => 'application/json',
    ];
});

test('an authenticated user can manage sales', function () {
    $client = Client::create(['name' => 'Sale Client']);

    $createResponse = $this
        ->withHeaders($this->headers)
        ->postJson('/api/sales', [
            'client_id' => $client->id,
            'status' => 'unpaid',
        ]);

    $saleId = $createResponse
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('sale.client.id', $client->id)
        ->assertJsonPath('sale.total', '0.00')
        ->assertJsonPath('sale.status', 'unpaid')
        ->json('sale.id');

    $this
        ->withHeaders($this->headers)
        ->getJson('/api/sales')
        ->assertOk()
        ->assertJsonPath('sales.0.id', $saleId);

    $this
        ->withHeaders($this->headers)
        ->getJson("/api/sales/{$saleId}")
        ->assertOk()
        ->assertJsonPath('sale.client.name', 'Sale Client');

    $this
        ->withHeaders($this->headers)
        ->patchJson("/api/sales/{$saleId}", [
            'status' => 'paid',
        ])
        ->assertOk()
        ->assertJsonPath('sale.status', 'paid');

    $this
        ->withHeaders($this->headers)
        ->deleteJson("/api/sales/{$saleId}")
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseMissing('sales', ['id' => $saleId]);
});

test('an authenticated user can manage sale items and stock is updated', function () {
    $client = Client::create(['name' => 'Item Sale Client']);
    $sale = Sale::create(['client_id' => $client->id, 'total' => 0]);
    $supplier = Supplier::create(['name' => 'Sale Supplier']);
    $category = Category::create(['name' => 'Sale Category']);
    $product = Product::create([
        'reference' => 'SI-001',
        'name' => 'Sale Product',
        'price' => 50,
        'stock' => 10,
        'category_id' => $category->id,
        'supplier_id' => $supplier->id,
    ]);

    $createResponse = $this
        ->withHeaders($this->headers)
        ->postJson('/api/sale-items', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

    $saleItemId = $createResponse
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('sale_item.product.id', $product->id)
        ->assertJsonPath('sale_item.price', '50.00')
        ->assertJsonPath('sale_item.total', '150.00')
        ->json('sale_item.id');

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'total' => '150.00',
    ]);
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 7,
    ]);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'quantity' => 3,
        'type' => 'out',
        'source' => 'sale',
        'reference_id' => $saleItemId,
    ]);

    $this
        ->withHeaders($this->headers)
        ->getJson('/api/sale-items')
        ->assertOk()
        ->assertJsonPath('sale_items.0.id', $saleItemId);

    $this
        ->withHeaders($this->headers)
        ->getJson("/api/sale-items/{$saleItemId}")
        ->assertOk()
        ->assertJsonPath('sale_item.product.name', 'Sale Product');

    $this
        ->withHeaders($this->headers)
        ->patchJson("/api/sale-items/{$saleItemId}", [
            'quantity' => 4,
        ])
        ->assertOk()
        ->assertJsonPath('sale_item.total', '200.00');

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'total' => '200.00',
    ]);
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 6,
    ]);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'quantity' => 1,
        'type' => 'out',
        'source' => 'sale',
        'reference_id' => $saleItemId,
    ]);

    $this
        ->withHeaders($this->headers)
        ->deleteJson("/api/sale-items/{$saleItemId}")
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseMissing('sale_items', ['id' => $saleItemId]);
    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'total' => '0.00',
    ]);
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 10,
    ]);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'quantity' => 4,
        'type' => 'in',
        'source' => 'sale',
        'reference_id' => $saleItemId,
    ]);
});

test('sale item cannot exceed available stock', function () {
    $client = Client::create(['name' => 'Stock Client']);
    $sale = Sale::create(['client_id' => $client->id, 'total' => 0]);
    $supplier = Supplier::create(['name' => 'Stock Supplier']);
    $category = Category::create(['name' => 'Stock Category']);
    $product = Product::create([
        'reference' => 'SI-002',
        'name' => 'Low Stock Product',
        'price' => 25,
        'stock' => 2,
        'category_id' => $category->id,
        'supplier_id' => $supplier->id,
    ]);

    $this
        ->withHeaders($this->headers)
        ->postJson('/api/sale-items', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 2,
    ]);
    $this->assertDatabaseMissing('sale_items', [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);
});

test('sale and sale item validation is enforced', function () {
    $this
        ->withHeaders($this->headers)
        ->postJson('/api/sales', [
            'client_id' => 999,
            'total' => 100,
            'status' => 'cancelled',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['client_id', 'total', 'status']);

    $this
        ->withHeaders($this->headers)
        ->postJson('/api/sale-items', [
            'sale_id' => 999,
            'product_id' => 999,
            'price' => -1,
            'quantity' => 0,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sale_id', 'product_id', 'price', 'quantity']);
});

test('sale api routes require authentication', function () {
    $client = Client::create(['name' => 'Guest Sale Client']);
    $sale = Sale::create(['client_id' => $client->id, 'total' => 0]);
    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => Product::create([
            'reference' => 'SI-003',
            'name' => 'Guest Sale Product',
            'price' => 100,
            'stock' => 5,
            'category_id' => Category::create(['name' => 'Guest Sale Category'])->id,
            'supplier_id' => Supplier::create(['name' => 'Guest Sale Supplier'])->id,
        ])->id,
        'price' => 20,
        'quantity' => 2,
        'total' => 40,
    ]);

    $this->getJson('/api/sales')->assertUnauthorized();
    $this->getJson('/api/sale-items')->assertUnauthorized();
});
