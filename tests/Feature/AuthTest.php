<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a user can register and receives a jwt token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password1',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('user.email', 'test@example.com')
        ->assertJsonPath('user.role', User::ROLE_EMPLOYEE)
        ->assertJsonStructure([
            'authorization' => ['token', 'type', 'expires_in'],
        ]);
});

test('a user can login, read their profile, refresh token, and logout', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('password1'),
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'password1',
    ]);

    $token = $loginResponse
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->json('authorization.token');

    $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('user.email', 'login@example.com');

    $refreshResponse = $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/refresh')
        ->assertOk()
        ->assertJsonStructure([
            'authorization' => ['token', 'type', 'expires_in'],
        ]);

    $refreshedToken = $refreshResponse->json('authorization.token');

    $this
        ->withHeader('Authorization', "Bearer {$refreshedToken}")
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('status', 'success');
});

test('a user can update their profile', function () {
    User::factory()->create([
        'email' => 'profile@example.com',
        'password' => Hash::make('password1'),
    ]);

    $token = $this->postJson('/api/login', [
        'email' => 'profile@example.com',
        'password' => 'password1',
    ])->json('authorization.token');

    $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/profile', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('user.name', 'Updated User')
        ->assertJsonPath('user.email', 'updated@example.com');
});

test('a user can update their role in their profile', function () {
    User::factory()->create([
        'email' => 'role-profile@example.com',
        'password' => Hash::make('password1'),
        'role' => User::ROLE_EMPLOYEE,
    ]);

    $token = $this->postJson('/api/login', [
        'email' => 'role-profile@example.com',
        'password' => 'password1',
    ])->json('authorization.token');

    $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/profile', [
            'role' => User::ROLE_ADMIN,
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('user.role', User::ROLE_ADMIN);
});

test('a user can reset their password', function () {
    User::factory()->create([
        'email' => 'password@example.com',
        'password' => Hash::make('password1'),
    ]);

    $token = $this->postJson('/api/login', [
        'email' => 'password@example.com',
        'password' => 'password1',
    ])->json('authorization.token');

    $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/password', [
            'current_password' => 'password1',
            'password' => 'newpass1',
            'password_confirmation' => 'newpass1',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->postJson('/api/login', [
        'email' => 'password@example.com',
        'password' => 'newpass1',
    ])->assertOk();
});
