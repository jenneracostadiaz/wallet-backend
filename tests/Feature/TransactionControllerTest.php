<?php

use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('TransactionsController', function () {

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

    /*
        it('can show a transaction', function () {
            $transaction = Transaction::factory()->for($this->user)->for($this->account)->for($this->category)->create();
            $response = $this->getJson(route('transactions.show', $transaction));
            $response->assertOk()->assertJsonStructure(['data']);
        });

        it('can create a transaction', function () {
            $data = [
                'type' => 'income',
                'amount' => 100.50,
                'account_id' => $this->account->id,
                'category_id' => $this->category->id,
                'date' => now()->format('Y-m-d H:i:s'),
                'description' => 'Test income',
            ];
            $response = $this->postJson(route('transactions.store'), $data);
            $response->assertCreated()->assertJsonStructure(['data']);
            $this->assertDatabaseHas('transactions', ['description' => 'Test income']);
        });

        it('can update a transaction', function () {
            $transaction = Transaction::factory()->for($this->user)->for($this->account)->for($this->category)->create();
            $data = [
                'type' => 'income',
                'amount' => 200.00,
                'account_id' => $this->account->id,
                'category_id' => $this->category->id,
                'date' => now()->format('Y-m-d H:i:s'),
                'description' => 'Updated',
            ];
            $response = $this->putJson(route('transactions.update', $transaction), $data);
            $response->assertOk()->assertJsonStructure(['data']);
            $this->assertDatabaseHas('transactions', ['description' => 'Updated']);
        });

        it('can delete a transaction', function () {
            $transaction = Transaction::factory()->for($this->user)->for($this->account)->for($this->category)->create();
            $response = $this->deleteJson(route('transactions.destroy', $transaction));
            $response->assertOk()->assertJsonFragment(['message' => 'Transaction deleted successfully']);
            $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
        });*/
});

