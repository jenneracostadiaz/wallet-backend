<?php

use App\Models\User;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->currency = Currency::factory()->create(['code' => 'USD']);
});

test('authenticated user can view accounts index', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('accounts.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn($page) => $page->component('accounts/index')
        ->has('accounts')
    );
});

test('user can create account', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('accounts.store'), [
            'name' => 'My Savings Account',
            'type' => 'savings',
            'currency' => $this->currency->id,
            'balance' => 5000.00,
            'description' => 'Emergency fund',
        ]);

    $response->assertRedirect(route('accounts.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('accounts', [
        'name' => 'My Savings Account',
        'type' => 'savings',
        'currency_id' => $this->currency->id,
        'balance' => 5000.00,
        'description' => 'Emergency fund',
        'user_id' => $this->user->id,
    ]);
});

test('account cannot be created without currency', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('accounts.store'), [
            'name' => 'My Savings Account',
            'type' => 'savings',
            'balance' => 5000.00,
            'description' => 'Emergency fund',
        ]);

    $response->assertSessionHasErrors('currency');
});

test('account cannot be created with invalid currency id', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('accounts.store'), [
            'name' => 'My Savings Account',
            'type' => 'savings',
            'currency' => 999999,
            'balance' => 5000.00,
            'description' => 'Emergency fund',
        ]);

    $response->assertSessionHasErrors('currency');
});

