<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
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

test('an authenticated user can manage purchases', function () {
    $supplier = Supplier::create(['name' => 'Purchase Supplier']);

    $createResponse = $this
        ->withHeaders($this->headers)
        ->postJson('/api/purchases', [
            'supplier_id' => $supplier->id,
            'status' => 'pending',
        ]);

    $purchaseId = $createResponse
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('purchase.supplier.id', $supplier->id)
        ->assertJsonPath('purchase.status', 'pending')
        ->json('purchase.id');

    $this
        ->withHeaders($this->headers)
        ->getJson('/api/purchases')
        ->assertOk()
        ->assertJsonPath('purchases.0.id', $purchaseId);

    $this
        ->withHeaders($this->headers)
        ->getJson("/api/purchases/{$purchaseId}")
        ->assertOk()
        ->assertJsonPath('purchase.supplier.name', 'Purchase Supplier');

    $this
        ->withHeaders($this->headers)
        ->patchJson("/api/purchases/{$purchaseId}", [
            'status' => 'confirmed',
        ])
        ->assertOk()
        ->assertJsonPath('purchase.status', 'confirmed');

    $this
        ->withHeaders($this->headers)
        ->deleteJson("/api/purchases/{$purchaseId}")
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseMissing('purchases', ['id' => $purchaseId]);
});

test('an authenticated user can manage purchase items', function () {
    $supplier = Supplier::create(['name' => 'Item Supplier']);
    $category = Category::create(['name' => 'Item Category']);
    $purchase = Purchase::create(['supplier_id' => $supplier->id]);
    $product = Product::create([
        'reference' => 'PI-001',
        'name' => 'Purchase Product',
        'price' => 100,
        'category_id' => $category->id,
        'supplier_id' => $supplier->id,
    ]);

    $createResponse = $this
        ->withHeaders($this->headers)
        ->postJson('/api/purchase-items', [
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

    $purchaseItemId = $createResponse
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('purchase_item.product.id', $product->id)
        ->assertJsonPath('purchase_item.price', '100.00')
        ->assertJsonPath('purchase_item.total', '300.00')
        ->json('purchase_item.id');

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'total' => '300.00',
    ]);
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 3,
    ]);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'quantity' => 3,
        'type' => 'in',
        'source' => 'purchase',
        'reference_id' => $purchaseItemId,
    ]);

    $this
        ->withHeaders($this->headers)
        ->getJson('/api/purchase-items')
        ->assertOk()
        ->assertJsonPath('purchase_items.0.id', $purchaseItemId);

    $this
        ->withHeaders($this->headers)
        ->getJson("/api/purchase-items/{$purchaseItemId}")
        ->assertOk()
        ->assertJsonPath('purchase_item.product.name', 'Purchase Product');

    $this
        ->withHeaders($this->headers)
        ->patchJson("/api/purchase-items/{$purchaseItemId}", [
            'quantity' => 4,
        ])
        ->assertOk()
        ->assertJsonPath('purchase_item.total', '400.00');

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'total' => '400.00',
    ]);
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 4,
    ]);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'quantity' => 1,
        'type' => 'in',
        'source' => 'purchase',
        'reference_id' => $purchaseItemId,
    ]);

    $this
        ->withHeaders($this->headers)
        ->deleteJson("/api/purchase-items/{$purchaseItemId}")
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseMissing('purchase_items', ['id' => $purchaseItemId]);
    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'total' => '0.00',
    ]);
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 0,
    ]);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'quantity' => 4,
        'type' => 'out',
        'source' => 'purchase',
        'reference_id' => $purchaseItemId,
    ]);
});

test('purchase and purchase item validation is enforced', function () {
    $this
        ->withHeaders($this->headers)
        ->postJson('/api/purchases', [
            'supplier_id' => 999,
            'status' => 'cancelled',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['supplier_id', 'status']);

    $this
        ->withHeaders($this->headers)
        ->postJson('/api/purchase-items', [
            'purchase_id' => 999,
            'product_id' => 999,
            'price' => -1,
            'quantity' => 0,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['purchase_id', 'product_id', 'price', 'quantity']);
});

test('purchase item product must belong to purchase supplier', function () {
    $purchaseSupplier = Supplier::create(['name' => 'Purchase Supplier']);
    $productSupplier = Supplier::create(['name' => 'Other Supplier']);
    $category = Category::create(['name' => 'Supplier Product Category']);
    $purchase = Purchase::create(['supplier_id' => $purchaseSupplier->id]);
    $product = Product::create([
        'reference' => 'PI-003',
        'name' => 'Other Supplier Product',
        'price' => 100,
        'category_id' => $category->id,
        'supplier_id' => $productSupplier->id,
    ]);

    $this
        ->withHeaders($this->headers)
        ->postJson('/api/purchase-items', [
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id']);
});

test('purchase api routes require authentication', function () {
    $supplier = Supplier::create(['name' => 'Guest Purchase Supplier']);
    $purchase = Purchase::create(['supplier_id' => $supplier->id]);
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => Product::create([
            'reference' => 'PI-002',
            'name' => 'Guest Purchase Product',
            'price' => 100,
            'category_id' => Category::create(['name' => 'Guest Item Category'])->id,
            'supplier_id' => $supplier->id,
        ])->id,
        'price' => 20,
        'quantity' => 2,
        'total' => 40,
    ]);

    $this->getJson('/api/purchases')->assertUnauthorized();
    $this->getJson('/api/purchase-items')->assertUnauthorized();
});
