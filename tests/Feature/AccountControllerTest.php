<?php

use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->currency = Currency::factory()->create();
    $this->actingAs($this->user);
});

test('index returns the authenticated users accounts', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'currency_id' => $this->currency->id,
    ]);
    $response = $this->getJson('/api/accounts');
    $response->assertOk()
        ->assertJsonFragment(['id' => $account->id]);
});

test('show returns an own account', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'currency_id' => $this->currency->id,
    ]);
    $response = $this->getJson("/api/accounts/{$account->id}");
    $response->assertOk()
        ->assertJsonFragment(['id' => $account->id]);
});

test('show returns 403 if the account does not belong to the user', function () {
    $other = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $other->id,
        'currency_id' => $this->currency->id,
    ]);
    $response = $this->getJson("/api/accounts/{$account->id}");
    $response->assertForbidden();
});

test('store creates an account', function () {
    $data = [
        'name' => 'Nueva Cuenta',
        'type' => 'checking',
        'balance' => 500,
        'currency_id' => $this->currency->id,
        'description' => 'Cuenta de prueba',
    ];
    $response = $this->postJson('/api/accounts', $data);
    $response->assertCreated()
        ->assertJsonFragment(['name' => 'Nueva Cuenta']);
    $this->assertDatabaseHas('accounts', ['name' => 'Nueva Cuenta']);
});

test('update updates an own account', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'currency_id' => $this->currency->id,
    ]);
    $data = [
        'name' => 'Cuenta Actualizada',
        'type' => 'checking',
        'balance' => 999,
        'currency_id' => $this->currency->id,
        'description' => 'Editada',
    ];
    $response = $this->putJson("/api/accounts/{$account->id}", $data);
    $response->assertOk()
        ->assertJsonFragment(['name' => 'Cuenta Actualizada']);
    $this->assertDatabaseHas('accounts', ['name' => 'Cuenta Actualizada']);
});

test('destroy deletes own account', function () {
    $account = Account::factory()->create([
        'user_id' => $this->user->id,
        'currency_id' => $this->currency->id,
    ]);
    $response = $this->deleteJson("/api/accounts/{$account->id}");
    $response->assertOk()
        ->assertJsonFragment(['message' => 'Account deleted successfully']);
    $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
});

test('destroy returns 403 if the account does not belong to the user', function () {
    $other = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $other->id,
        'currency_id' => $this->currency->id,
    ]);
    $response = $this->deleteJson("/api/accounts/{$account->id}");
    $response->assertForbidden();
});
