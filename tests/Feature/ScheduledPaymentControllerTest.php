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
        'payment_type' => PaymentType::OneTime->value, // Changed to one_time to avoid schedule requirement
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
        'payment_type' => PaymentType::OneTime->value, // Changed to one_time
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

test('can execute a payment manually', function () {
    $scheduledPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'amount' => 100.00,
    ]);
    
    $originalBalance = $this->account->balance;
    
    $response = $this->postJson("/api/scheduled-payments/{$scheduledPayment->id}/execute", [
        'amount' => 100.00,
        'notes' => 'Manual payment execution test'
    ]);
    
    $response->assertOk()
        ->assertJsonFragment(['message' => 'Pago ejecutado exitosamente']);
    
    // Check transaction was created
    $this->assertDatabaseHas('transactions', [
        'amount' => 100.00,
        'account_id' => $this->account->id,
        'type' => 'expense',
    ]);
    
    // Check payment history was created
    $this->assertDatabaseHas('payment_history', [
        'scheduled_payment_id' => $scheduledPayment->id,
        'amount' => 100.00,
        'status' => 'paid',
    ]);
    
    // Check account balance was updated
    $this->account->refresh();
    expect((float) $this->account->balance)->toBe($originalBalance - 100.00);
});

test('can get upcoming payments', function () {
    // Create payment due today
    $todayPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'next_payment_date' => today(),
        'status' => PaymentStatus::Active,
    ]);
    
    // Create payment due tomorrow
    $tomorrowPayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'next_payment_date' => today()->addDay(),
        'status' => PaymentStatus::Active,
    ]);
    
    // Create overdue payment
    $overduePayment = ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'next_payment_date' => today()->subDay(),
        'status' => PaymentStatus::Active,
    ]);
    
    $response = $this->getJson('/api/scheduled-payments/upcoming');
    
    $response->assertOk();
    
    $data = $response->json();
    
    expect($data['count'])->toBe(3);
    expect($data['total_amount'])->toBeGreaterThan(0);
    expect($data)->toHaveKeys(['overdue', 'today', 'tomorrow', 'upcoming']);
});

test('can get payment notifications', function () {
    // Create overdue payment
    ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'next_payment_date' => today()->subDay(),
        'status' => PaymentStatus::Active,
        'amount' => 50.00,
    ]);
    
    // Create payment due today
    ScheduledPayment::factory()->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'next_payment_date' => today(),
        'status' => PaymentStatus::Active,
        'amount' => 75.00,
    ]);
    
    $response = $this->getJson('/api/scheduled-payments/notifications');
    
    $response->assertOk();
    
    $data = $response->json();
    
    expect($data['has_notifications'])->toBe(true);
    expect($data['messages'])->toBeArray();
    expect($data['summary'])->toHaveKeys(['overdue', 'due_today', 'upcoming_week']);
});

test('can create recurring payment with schedule', function () {
    $data = [
        'name' => 'Netflix Monthly',
        'payment_type' => PaymentType::Recurring->value,
        'amount' => 35.90,
        'start_date' => now()->format('Y-m-d'),
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'schedule' => [
            'frequency' => 'monthly',
            'interval' => 1,
            'day_of_month' => 15,
            'days_before_notification' => 3,
        ]
    ];
    
    $response = $this->postJson('/api/scheduled-payments', $data);
    
    $response->assertCreated();
    
    $payment = ScheduledPayment::latest()->first();
    
    // Check payment was created
    expect($payment->name)->toBe('Netflix Monthly');
    
    // Check schedule was created
    $this->assertDatabaseHas('payment_schedules', [
        'scheduled_payment_id' => $payment->id,
        'frequency' => 'monthly',
        'interval' => 1,
        'day_of_month' => 15,
        'auto_process' => false, // Should be manual
    ]);
});

test('can create debt payment with debt details', function () {
    $data = [
        'name' => 'Credit Card Debt',
        'payment_type' => PaymentType::Debt->value,
        'amount' => 200.00,
        'start_date' => now()->format('Y-m-d'),
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'debt' => [
            'original_amount' => 2000.00,
            'total_installments' => 10,
            'installment_amount' => 200.00,
            'creditor' => 'VISA Bank',
            'reference_number' => 'CC-12345',
        ]
    ];
    
    $response = $this->postJson('/api/scheduled-payments', $data);
    
    $response->assertCreated();
    
    $payment = ScheduledPayment::latest()->first();
    
    // Check debt details were created
    $this->assertDatabaseHas('debt_details', [
        'scheduled_payment_id' => $payment->id,
        'original_amount' => 2000.00,
        'remaining_amount' => 2000.00,
        'total_installments' => 10,
        'creditor' => 'VISA Bank',
    ]);
});
