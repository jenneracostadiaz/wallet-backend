<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Transactions', function () {

    it('can list transactions', function () {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user)->for($currency)->create();
        $category = Category::factory()->for($user)->create(['type' => 'income']);
        Transaction::factory()->for($user)->for($account)->for($category)->create();

        $this->actingAs($user)
            ->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'type', 'amount', 'date', 'description', 'account', 'currency']]]);
    });

    it('can show a transaction', function () {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user)->for($currency)->create();
        $category = Category::factory()->for($user)->create(['type' => 'income']);
        $transaction = Transaction::factory()->for($user)->for($account)->for($category)->create();

        $this->actingAs($user)
            ->getJson(route('transactions.show', $transaction))
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'type', 'amount', 'date', 'description', 'account', 'currency']]);
    });

    it('cannot show a transaction for another user', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user1)->for($currency)->create();
        $category = Category::factory()->for($user1)->create(['type' => 'income']);
        $transaction = Transaction::factory()->for($user1)->for($account)->for($category)->create();

        $this->actingAs($user2)
            ->getJson(route('transactions.show', $transaction))
            ->assertForbidden();
    });

    it('can create a transaction', function () {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user)->for($currency)->create();
        $category = Category::factory()->for($user)->create(['type' => 'income']);

        $data = [
            'type' => 'income',
            'amount' => 100.50,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'description' => 'Test income',
        ];

        $this->actingAs($user)
            ->postJson('/api/transactions', $data)
            ->assertCreated()
            ->assertJsonStructure(['data'])
            ->assertJsonFragment(['description' => 'Test income']);

        $this->assertDatabaseHas('transactions', ['description' => 'Test income']);
    });

    it('cannot create a transaction with invalid ammount', function () {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user)->for($currency)->create();
        $category = Category::factory()->for($user)->create(['type' => 'income']);

        $data = [
            'type' => 'income',
            'amount' => -50.00, // Invalid amount
            'account_id' => $account->id,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'description' => 'Invalid transaction',
        ];

        $this->actingAs($user)
            ->postJson('/api/transactions', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });

    it('cannot create a transaction with missing fields', function () {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user)->for($currency)->create();
        $category = Category::factory()->for($user)->create(['type' => 'income']);

        $data = [
            'type' => 'income',
            // Missing amount, account_id, category_id, date, description
        ];

        $this->actingAs($user)
            ->postJson('/api/transactions', $data)
            ->assertStatus(422);
    });

    it('cannot create a transaction for another user', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user1)->for($currency)->create();
        $category = Category::factory()->for($user1)->create(['type' => 'income']);

        $data = [
            'type' => 'income',
            'amount' => 100.00,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'description' => 'Test income',
        ];

        $this->actingAs($user2)
            ->postJson('/api/transactions', $data)
            ->assertForbidden();
    });

    it('cannot create a transaction if category type does not match transaction type', function () {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user)->for($currency)->create();
        $category = Category::factory()->for($user)->create(['type' => 'expense']);

        $data = [
            'type' => 'income', // Does not match the type of the category
            'amount' => 100.00,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'description' => 'Test type mismatch',
        ];

        $this->actingAs($user)
            ->postJson('/api/transactions', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    });

    it('can update a transaction', function () {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user)->for($currency)->create();
        $category = Category::factory()->for($user)->create(['type' => 'income']);
        $transaction = Transaction::factory()->for($user)->for($account)->for($category)->create();

        $data = [
            'type' => 'income',
            'amount' => 200.00,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'description' => 'Updated',
        ];

        $this->actingAs($user)
            ->putJson(route('transactions.update', $transaction), $data)
            ->assertOk()
            ->assertJsonStructure(['data'])
            ->assertJsonFragment(['description' => 'Updated']);

        $this->assertDatabaseHas('transactions', ['description' => 'Updated']);
    });

    it('can delete a transaction', function () {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $account = Account::factory()->for($user)->for($currency)->create();
        $category = Category::factory()->for($user)->create(['type' => 'income']);
        $transaction = Transaction::factory()->for($user)->for($account)->for($category)->create();

        $this->actingAs($user)
            ->deleteJson(route('transactions.destroy', $transaction))
            ->assertOk()
            ->assertJsonFragment(['message' => 'Transaction deleted successfully']);

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    });
});
