<?php

use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can set initial balance', function () {
    $user = User::factory()->create();
    $currency = Currency::factory()->create();

    $account = new Account([
        'name' => 'My Account',
        'type' => AccountType::Checking,
        'currency_id' => $currency->id,
        'description' => 'My personal account',
        'user_id' => $user->id,
    ]);

    $account->setInitialBalance(1000.50);

    expect($account->balance)->toBe(1000.50);
});

test('belongs to a user', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    expect($account->user)
        ->toBeInstanceOf(User::class)
        ->and($account->user->id)->toBe($user->id);
});
