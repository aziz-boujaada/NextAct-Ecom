<?php

use App\Models\Client;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();

    $this->headers = [
        'Authorization' => 'Bearer '.auth('api')->login($user),
    ];
});

test('an authenticated user can manage clients', function () {
    $createResponse = $this
        ->withHeaders($this->headers)
        ->postJson('/api/clients', [
            'name' => 'Acme Retail',
            'phone' => '+212600000001',
            'address' => 'Casablanca',
        ]);

    $clientId = $createResponse
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('client.name', 'Acme Retail')
        ->json('client.id');

    $this
        ->withHeaders($this->headers)
        ->getJson('/api/clients')
        ->assertOk()
        ->assertJsonPath('clients.0.id', $clientId);

    $this
        ->withHeaders($this->headers)
        ->getJson("/api/clients/{$clientId}")
        ->assertOk()
        ->assertJsonPath('client.phone', '+212600000001');

    $this
        ->withHeaders($this->headers)
        ->putJson("/api/clients/{$clientId}", [
            'name' => 'Acme Retail Updated',
        ])
        ->assertOk()
        ->assertJsonPath('client.name', 'Acme Retail Updated');

    $this
        ->withHeaders($this->headers)
        ->deleteJson("/api/clients/{$clientId}")
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseMissing('clients', ['id' => $clientId]);
});

test('an authenticated user can manage suppliers', function () {
    $createResponse = $this
        ->withHeaders($this->headers)
        ->postJson('/api/suppliers', [
            'name' => 'Main Supplier',
            'phone' => '+212600000002',
            'address' => 'Rabat',
        ]);

    $supplierId = $createResponse
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('supplier.name', 'Main Supplier')
        ->json('supplier.id');

    $this
        ->withHeaders($this->headers)
        ->getJson('/api/suppliers')
        ->assertOk()
        ->assertJsonPath('suppliers.0.id', $supplierId);

    $this
        ->withHeaders($this->headers)
        ->getJson("/api/suppliers/{$supplierId}")
        ->assertOk()
        ->assertJsonPath('supplier.phone', '+212600000002');

    $this
        ->withHeaders($this->headers)
        ->patchJson("/api/suppliers/{$supplierId}", [
            'address' => 'Marrakech',
        ])
        ->assertOk()
        ->assertJsonPath('supplier.address', 'Marrakech');

    $this
        ->withHeaders($this->headers)
        ->deleteJson("/api/suppliers/{$supplierId}")
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseMissing('suppliers', ['id' => $supplierId]);
});

test('client and supplier names are required when creating records', function () {
    $this
        ->withHeaders($this->headers)
        ->postJson('/api/clients', ['phone' => '+212600000003'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $this
        ->withHeaders($this->headers)
        ->postJson('/api/suppliers', ['address' => 'Tangier'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('client and supplier api routes require authentication', function () {
    Client::create(['name' => 'Guest Client']);
    Supplier::create(['name' => 'Guest Supplier']);

    $this->getJson('/api/clients')->assertUnauthorized();
    $this->getJson('/api/suppliers')->assertUnauthorized();
});
