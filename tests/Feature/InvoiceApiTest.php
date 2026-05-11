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
        'Authorization' => 'Bearer ' . auth('api')->login($user),
        'Accept' => 'application/pdf',
    ];
});

test('an authenticated user can export a sale invoice as pdf', function () {
    $client = Client::create([
        'name' => 'Invoice Client',
        'phone' => '123456789',
        'address' => 'Main Street',
    ]);

    $sale = Sale::create([
        'reference' => 'SALE-INV-001',
        'client_id' => $client->id,
        'total' => 150,
        'status' => 'paid',
    ]);

    $supplier = Supplier::create(['name' => 'Invoice Supplier']);
    $category = Category::create(['name' => 'Invoice Category']);
    $product = Product::create([
        'reference' => 'INV-PROD-1',
        'name' => 'Invoice Product',
        'price' => 50,
        'stock' => 10,
        'category_id' => $category->id,
        'supplier_id' => $supplier->id,
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'price' => 50,
        'quantity' => 3,
        'total' => 150,
    ]);

    $response = $this
        ->withHeaders($this->headers)
        ->get('/api/invoice/' . $sale->id);

    $response->assertOk();

    expect($response->headers->get('content-type'))->toStartWith('application/pdf');
    expect(substr($response->content(), 0, 4))->toBe('%PDF');

    $this->assertDatabaseHas('invoices', [
        'sale_id' => $sale->id,
        'invoice_number' => 'INV-SALE-INV-001',
        'total' => '150.00',
    ]);
});