<?php

use App\Models\Account;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('has many accounts', function () {
    $currency = Currency::factory()->create();
    Account::factory()->count(3)->create(['currency_id' => $currency->id]);

    expect($currency->accounts)->toHaveCount(3)
        ->and($currency->accounts->first())->toBeInstanceOf(Account::class);
});

test('decimal places is cast to integer', function () {
   $currency = Currency::factory()->create(['decimal_places' => 2.5]);
    expect($currency->decimal_places)->toBeInt
        ->and($currency->decimal_places)->toEqual(2);
});

test('symbol is cast to string', function () {
    $currency = Currency::factory()->create(['symbol' => '$']);
    expect($currency->symbol)->toBeString
        ->and($currency->symbol)->toEqual('$');
});

test('is_active is cast to boolean', function () {
    $currency = Currency::factory()->create(['is_active' => '1']);
    expect($currency->is_active)->toBeBool
        ->and($currency->is_active)->toEqual(true);
});
