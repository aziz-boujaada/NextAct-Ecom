<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();

    $this->headers = [
        'Authorization' => 'Bearer '.auth('api')->login($user),
        'Accept' => 'application/json',
    ];
});

test('an authenticated user can manage categories', function () {
    $createResponse = $this
        ->withHeaders($this->headers)
        ->postJson('/api/categories', [
            'name' => 'Shoes',
            'description' => 'Footwear category',
        ]);

    $categoryId = $createResponse
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('category.name', 'Shoes')
        ->json('category.id');

    $this
        ->withHeaders($this->headers)
        ->getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('categories.0.id', $categoryId);

    $this
        ->withHeaders($this->headers)
        ->getJson("/api/categories/{$categoryId}")
        ->assertOk()
        ->assertJsonPath('category.description', 'Footwear category');

    $this
        ->withHeaders($this->headers)
        ->patchJson("/api/categories/{$categoryId}", [
            'description' => 'Updated footwear category',
        ])
        ->assertOk()
        ->assertJsonPath('category.description', 'Updated footwear category');

    $this
        ->withHeaders($this->headers)
        ->deleteJson("/api/categories/{$categoryId}")
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
});

test('an authenticated user can manage products', function () {
    Storage::fake('public');

    $category = Category::create(['name' => 'Accessories']);
    $supplier = Supplier::create(['name' => 'Warehouse One']);
    $image = UploadedFile::fake()->image('belt.jpg');

    $createResponse = $this
        ->withHeaders($this->headers)
        ->post('/api/products', [
            'reference' => 'SKU-001',
            'name' => 'Leather Belt',
            'description' => 'Black leather belt',
            'image' => $image,
            'price' => 149.90,
            'stock' => 25,
            'min_stock' => 5,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ]);

    $productId = $createResponse
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('product.reference', 'SKU-001')
        ->assertJsonPath('product.category.id', $category->id)
        ->assertJsonPath('product.supplier.id', $supplier->id)
        ->json('product.id');

    $createdImagePath = $createResponse->json('product.image_path');
    Storage::disk('public')->assertExists($createdImagePath);

    $this
        ->withHeaders($this->headers)
        ->getJson('/api/products')
        ->assertOk()
        ->assertJsonPath('products.0.id', $productId);

    $this
        ->withHeaders($this->headers)
        ->getJson("/api/products/{$productId}")
        ->assertOk()
        ->assertJsonPath('product.name', 'Leather Belt');

    $newImage = UploadedFile::fake()->image('belt-updated.jpg');

    $updateResponse = $this
        ->withHeaders($this->headers)
        ->post("/api/products/{$productId}", [
            '_method' => 'PUT',
            'reference' => 'SKU-001',
            'name' => 'Leather Belt Updated',
            'image' => $newImage,
            'price' => 159.90,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ])
        ->assertOk()
        ->assertJsonPath('product.name', 'Leather Belt Updated')
        ->assertJsonPath('product.price', '159.90');

    $updatedImagePath = $updateResponse->json('product.image_path');
    Storage::disk('public')->assertMissing($createdImagePath);
    Storage::disk('public')->assertExists($updatedImagePath);

    $this
        ->withHeaders($this->headers)
        ->deleteJson("/api/products/{$productId}")
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseMissing('products', ['id' => $productId]);
    Storage::disk('public')->assertMissing($updatedImagePath);
});

test('product and category validation is enforced', function () {
    $this
        ->withHeaders($this->headers)
        ->postJson('/api/categories', ['description' => 'Missing name'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $this
        ->withHeaders($this->headers)
        ->postJson('/api/products', [
            'reference' => 'SKU-002',
            'name' => 'Invalid Product',
            'price' => -1,
            'category_id' => 999,
            'supplier_id' => 999,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['price', 'category_id', 'supplier_id']);
});

test('product references must be unique', function () {
    $category = Category::create(['name' => 'Bags']);
    $supplier = Supplier::create(['name' => 'Warehouse Two']);

    Product::create([
        'reference' => 'SKU-003',
        'name' => 'Canvas Bag',
        'price' => 79.90,
        'category_id' => $category->id,
        'supplier_id' => $supplier->id,
    ]);

    $this
        ->withHeaders($this->headers)
        ->postJson('/api/products', [
            'reference' => 'SKU-003',
            'name' => 'Duplicate Bag',
            'price' => 89.90,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['reference']);
});

test('products can be filtered by supplier', function () {
    $category = Category::create(['name' => 'Supplier Filter Category']);
    $supplier = Supplier::create(['name' => 'Included Supplier']);
    $otherSupplier = Supplier::create(['name' => 'Excluded Supplier']);
    $includedProduct = Product::create([
        'reference' => 'SKU-005',
        'name' => 'Included Product',
        'price' => 49.90,
        'category_id' => $category->id,
        'supplier_id' => $supplier->id,
    ]);
    Product::create([
        'reference' => 'SKU-006',
        'name' => 'Excluded Product',
        'price' => 59.90,
        'category_id' => $category->id,
        'supplier_id' => $otherSupplier->id,
    ]);

    $this
        ->withHeaders($this->headers)
        ->getJson("/api/products?supplier_id={$supplier->id}")
        ->assertOk()
        ->assertJsonCount(1, 'products')
        ->assertJsonPath('products.0.id', $includedProduct->id)
        ->assertJsonPath('products.0.supplier.id', $supplier->id);
});

test('product and category api routes require authentication', function () {
    $category = Category::create(['name' => 'Guest Category']);
    $supplier = Supplier::create(['name' => 'Guest Supplier']);

    Product::create([
        'reference' => 'SKU-004',
        'name' => 'Guest Product',
        'price' => 99.90,
        'category_id' => $category->id,
        'supplier_id' => $supplier->id,
    ]);

    $this->getJson('/api/categories')->assertUnauthorized();
    $this->getJson('/api/products')->assertUnauthorized();
});
