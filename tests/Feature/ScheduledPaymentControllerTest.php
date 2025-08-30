<?php

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\ScheduledPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->currency = Currency::factory()->create();
    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
        'currency_id' => $this->currency->id,
    ]);
    $this->category = Category::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->actingAs($this->user);
});

test('index returns the authenticated users scheduled payments', function () {
    $scheduledPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
    ]);
    
    $response = $this->getJson('/api/scheduled-payments');
    
    $response->assertOk()
        ->assertJsonFragment(['id' => $scheduledPayment->id]);
});

test('show returns an own scheduled payment', function () {
    $scheduledPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
    ]);
    
    $response = $this->getJson("/api/scheduled-payments/{$scheduledPayment->id}");
    
    $response->assertOk()
        ->assertJsonFragment(['id' => $scheduledPayment->id]);
});

test('show fails for other users scheduled payment', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
        'currency_id' => $this->currency->id,
    ]);
    $scheduledPayment = ScheduledPayment::factory()->create([
        'user_id' => $otherUser->id,
        'account_id' => $otherAccount->id,
    ]);
    
    $response = $this->getJson("/api/scheduled-payments/{$scheduledPayment->id}");
    
    $response->assertForbidden();
});

test('store creates a new scheduled payment', function () {
    $data = [
        'name' => 'Netflix Subscription',
        'description' => 'Monthly Netflix subscription',
        'payment_type' => PaymentType::Recurring->value,
        'status' => PaymentStatus::Active->value,
        'amount' => 35.90,
        'color' => '#E50914',
        'icon' => '📺',
        'start_date' => now()->format('Y-m-d'),
        'next_payment_date' => now()->addMonth()->format('Y-m-d'),
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
    ];
    
    $response = $this->postJson('/api/scheduled-payments', $data);
    
    $response->assertCreated()
        ->assertJsonFragment(['name' => 'Netflix Subscription']);
    
    $this->assertDatabaseHas('scheduled_payments', [
        'name' => 'Netflix Subscription',
        'user_id' => $this->user->id,
    ]);
});

test('store fails with invalid account', function () {
    $otherUser = User::factory()->create();
    $otherAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
        'currency_id' => $this->currency->id,
    ]);
    
    $data = [
        'name' => 'Netflix Subscription',
        'payment_type' => PaymentType::Recurring->value,
        'amount' => 35.90,
        'start_date' => now()->format('Y-m-d'),
        'account_id' => $otherAccount->id, // Other user's account
    ];
    
    $response = $this->postJson('/api/scheduled-payments', $data);
    
    $response->assertNotFound();
});

test('update modifies an existing scheduled payment', function () {
    $scheduledPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'name' => 'Original Name',
    ]);
    
    $data = [
        'name' => 'Updated Name',
        'amount' => 50.00,
    ];
    
    $response = $this->putJson("/api/scheduled-payments/{$scheduledPayment->id}", $data);
    
    $response->assertOk()
        ->assertJsonFragment(['name' => 'Updated Name']);
    
    $this->assertDatabaseHas('scheduled_payments', [
        'id' => $scheduledPayment->id,
        'name' => 'Updated Name',
        'amount' => 50.00,
    ]);
});

test('destroy deletes a scheduled payment', function () {
    $scheduledPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
    ]);
    
    $response = $this->deleteJson("/api/scheduled-payments/{$scheduledPayment->id}");
    
    $response->assertOk()
        ->assertJsonFragment(['message' => 'Pago programado eliminado exitosamente']);
    
    $this->assertDatabaseMissing('scheduled_payments', [
        'id' => $scheduledPayment->id,
    ]);
});

test('index can filter by status', function () {
    $activePayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'status' => PaymentStatus::Active,
    ]);
    
    $pausedPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'status' => PaymentStatus::Paused,
    ]);
    
    $response = $this->getJson('/api/scheduled-payments?status=' . PaymentStatus::Active->value);
    
    $response->assertOk();
    
    $responseData = $response->json('data');
    $paymentIds = collect($responseData)->pluck('id')->toArray();
    
    expect($paymentIds)->toContain($activePayment->id);
    expect($paymentIds)->not->toContain($pausedPayment->id);
});

test('index can search by name', function () {
    $netflixPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'name' => 'Netflix Subscription',
    ]);
    
    $spotifyPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'name' => 'Spotify Premium',
    ]);
    
    $response = $this->getJson('/api/scheduled-payments?search=Netflix');
    
    $response->assertOk();
    
    $responseData = $response->json('data');
    $paymentIds = collect($responseData)->pluck('id')->toArray();
    
    expect($paymentIds)->toContain($netflixPayment->id);
    expect($paymentIds)->not->toContain($spotifyPayment->id);
});
