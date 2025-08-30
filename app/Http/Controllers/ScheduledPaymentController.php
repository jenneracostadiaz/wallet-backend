<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduledPaymentRequest;
use App\Http\Requests\UpdateScheduledPaymentRequest;
use App\Http\Resources\ScheduledPaymentResource;
use App\Models\ScheduledPayment;
use App\Services\PaymentNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScheduledPaymentController extends Controller
{
    /**
     * Display a listing of the scheduled payments.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = auth()->user()
            ->scheduledPayments()
            ->with(['account', 'category', 'paymentSchedule', 'debtDetail'])
            ->orderBy('order')
            ->orderBy('next_payment_date');

        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply search if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $scheduledPayments = $query->get();

        return ScheduledPaymentResource::collection($scheduledPayments);
    }

    /**
     * Store a newly created scheduled payment.
     */
    public function store(StoreScheduledPaymentRequest $request): JsonResponse
    {
        // Verify account belongs to user
        $account = auth()->user()->accounts()->findOrFail($request->account_id);

        // Verify category belongs to user (if provided)
        if ($request->category_id) {
            auth()->user()->categories()->findOrFail($request->category_id);
        }

        $scheduledPayment = auth()->user()->scheduledPayments()->create([
            ...$request->safe()->except(['schedule', 'debt']),
            'order' => $this->getNextOrder(),
        ]);

        // Create PaymentSchedule if it's a recurring payment
        if ($request->payment_type === 'recurring' && $request->has('schedule')) {
            $scheduleData = $request->input('schedule');
            $scheduledPayment->paymentSchedule()->create([
                'frequency' => $scheduleData['frequency'] ?? null,
                'interval' => $scheduleData['interval'] ?? 1,
                'day_of_month' => $scheduleData['day_of_month'] ?? null,
                'day_of_week' => $scheduleData['day_of_week'] ?? null,
                'max_occurrences' => $scheduleData['max_occurrences'] ?? null,
                'occurrences_count' => 0,
                'auto_process' => false, // Siempre manual
                'days_before_notification' => $scheduleData['days_before_notification'] ?? 3,
                'create_transaction' => true, // Crear transacción cuando se ejecute
            ]);
        }

        // Create DebtDetail if it's a debt payment
        if ($request->payment_type === 'debt' && $request->has('debt')) {
            $debtData = $request->input('debt');
            $originalAmount = $debtData['original_amount'] ?? $request->amount;
            
            $scheduledPayment->debtDetail()->create([
                'original_amount' => $originalAmount,
                'remaining_amount' => $originalAmount,
                'paid_amount' => 0,
                'total_installments' => $debtData['total_installments'] ?? 1,
                'paid_installments' => 0,
                'installment_amount' => $debtData['installment_amount'] ?? $request->amount,
                'interest_rate' => $debtData['interest_rate'] ?? 0,
                'creditor' => $debtData['creditor'] ?? null,
                'reference_number' => $debtData['reference_number'] ?? null,
                'due_date' => $debtData['due_date'] ?? null,
                'late_fee' => 0,
                'days_overdue' => 0,
            ]);
        }

        // Load relationships for response
        $scheduledPayment->load(['account.currency', 'category', 'paymentSchedule', 'debtDetail']);

        return (new ScheduledPaymentResource($scheduledPayment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified scheduled payment.
     */
    public function show(ScheduledPayment $scheduledPayment): ScheduledPaymentResource
    {
        $this->authorize('view', $scheduledPayment);

        // Load all relationships including payment history
        $scheduledPayment->load([
            'account.currency',
            'category',
            'paymentSchedule',
            'debtDetail',
            'paymentHistory' => function ($query) {
                $query->orderBy('scheduled_date', 'desc')->limit(10);
            },
            'paymentHistory.transaction'
        ]);

        return new ScheduledPaymentResource($scheduledPayment);
    }

    /**
     * Update the specified scheduled payment.
     */
    public function update(UpdateScheduledPaymentRequest $request, ScheduledPayment $scheduledPayment): ScheduledPaymentResource
    {
        $this->authorize('update', $scheduledPayment);

        // Verify account belongs to user (if being updated)
        if ($request->has('account_id')) {
            auth()->user()->accounts()->findOrFail($request->account_id);
        }

        // Verify category belongs to user (if being updated)
        if ($request->has('category_id') && $request->category_id) {
            auth()->user()->categories()->findOrFail($request->category_id);
        }

        $scheduledPayment->update($request->validated());

        // Load relationships for response
        $scheduledPayment->load(['account', 'category', 'paymentSchedule', 'debtDetail']);

        return new ScheduledPaymentResource($scheduledPayment);
    }

    /**
     * Remove the specified scheduled payment.
     */
    public function destroy(ScheduledPayment $scheduledPayment): JsonResponse
    {
        $this->authorize('delete', $scheduledPayment);

        // Check if payment has history - maybe warn user?
        $hasHistory = $scheduledPayment->paymentHistory()->exists();

        $scheduledPayment->delete();

        return response()->json([
            'message' => 'Pago programado eliminado exitosamente',
            'had_history' => $hasHistory,
        ]);
    }

    /**
     * Get the next order number for the user's scheduled payments.
     */
    private function getNextOrder(): int
    {
        return auth()->user()->scheduledPayments()->max('order') + 1;
    }

    /**
     * Execute a scheduled payment manually.
     */
    public function executePayment(ScheduledPayment $scheduledPayment, Request $request): JsonResponse
    {
        $this->authorize('update', $scheduledPayment);

        // Validate the optional amount
        $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = $request->input('amount', $scheduledPayment->amount);
        $notes = $request->input('notes');

        try {
            // Create the transaction
            $transaction = auth()->user()->transactions()->create([
                'amount' => $amount,
                'description' => "Pago: {$scheduledPayment->name}",
                'date' => now(),
                'type' => 'expense',
                'account_id' => $scheduledPayment->account_id,
                'category_id' => $scheduledPayment->category_id,
                'to_account_id' => null,
            ]);

            // Update account balance
            $scheduledPayment->account->decrement('balance', $amount);

            // Create payment history record
            $paymentHistory = $scheduledPayment->paymentHistory()->create([
                'amount' => $amount,
                'planned_amount' => $scheduledPayment->amount,
                'status' => 'paid',
                'scheduled_date' => $scheduledPayment->next_payment_date ?? now(),
                'processed_date' => now(),
                'transaction_id' => $transaction->id,
                'notes' => $notes,
            ]);

            // Update debt details if it's a debt payment
            if ($scheduledPayment->payment_type->value === 'debt' && $scheduledPayment->debtDetail) {
                $debtDetail = $scheduledPayment->debtDetail;
                $debtDetail->paid_amount += $amount;
                $debtDetail->remaining_amount = max(0, $debtDetail->remaining_amount - $amount);
                $debtDetail->paid_installments += 1;
                
                // Check if debt is fully paid
                if ($debtDetail->remaining_amount <= 0) {
                    $scheduledPayment->update(['status' => 'completed']);
                }
                
                $debtDetail->save();
            }

            // Update next payment date for recurring payments
            if ($scheduledPayment->payment_type->value === 'recurring' && $scheduledPayment->paymentSchedule) {
                $schedule = $scheduledPayment->paymentSchedule;
                $schedule->occurrences_count += 1;
                
                // Calculate next payment date
                $nextDate = $schedule->calculateNextPaymentDate(now());
                $scheduledPayment->update(['next_payment_date' => $nextDate]);
                
                // Check if max occurrences reached
                if ($schedule->max_occurrences && $schedule->occurrences_count >= $schedule->max_occurrences) {
                    $scheduledPayment->update(['status' => 'completed']);
                }
                
                $schedule->save();
            }

            // For one-time payments, mark as completed
            if ($scheduledPayment->payment_type->value === 'one_time') {
                $scheduledPayment->update(['status' => 'completed']);
            }

            return response()->json([
                'message' => 'Pago ejecutado exitosamente',
                'payment_history' => new \App\Http\Resources\PaymentHistoryResource($paymentHistory->load('transaction')),
                'transaction' => new \App\Http\Resources\TransactionResource($transaction),
                'updated_payment' => new ScheduledPaymentResource($scheduledPayment->fresh(['account', 'category', 'paymentSchedule', 'debtDetail'])),
            ]);

        } catch (\Exception $e) {
            // Create failed payment history record
            $scheduledPayment->paymentHistory()->create([
                'amount' => 0,
                'planned_amount' => $scheduledPayment->amount,
                'status' => 'failed',
                'scheduled_date' => $scheduledPayment->next_payment_date ?? now(),
                'processed_date' => now(),
                'failure_reason' => $e->getMessage(),
                'notes' => $notes,
            ]);

            return response()->json([
                'message' => 'Error al ejecutar el pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get upcoming payments that need attention.
     */
    public function upcomingPayments(Request $request): JsonResponse
    {
        $days = $request->input('days', 7); // Next 7 days by default

        $upcomingPayments = auth()->user()
            ->scheduledPayments()
            ->with(['account.currency', 'category', 'paymentSchedule', 'debtDetail'])
            ->where('status', 'active')
            ->where(function($query) use ($days) {
                $query->whereDate('next_payment_date', '<=', now()->addDays($days))
                      ->orWhereDate('next_payment_date', '<=', now()); // Overdue
            })
            ->orderBy('next_payment_date')
            ->get();

        $grouped = $upcomingPayments->groupBy(function($payment) {
            $date = $payment->next_payment_date;
            if ($date && $date->isToday()) {
                return 'today';
            } elseif ($date && $date->isPast()) {
                return 'overdue';
            } elseif ($date && $date->isTomorrow()) {
                return 'tomorrow';
            } else {
                return 'upcoming';
            }
        });

        return response()->json([
            'overdue' => ScheduledPaymentResource::collection($grouped->get('overdue', collect())),
            'today' => ScheduledPaymentResource::collection($grouped->get('today', collect())),
            'tomorrow' => ScheduledPaymentResource::collection($grouped->get('tomorrow', collect())),
            'upcoming' => ScheduledPaymentResource::collection($grouped->get('upcoming', collect())),
            'total_amount' => $upcomingPayments->sum('amount'),
            'count' => $upcomingPayments->count(),
        ]);
    }

    /**
     * Get payment notifications for the current user.
     */
    public function notifications(PaymentNotificationService $notificationService): JsonResponse
    {
        $summary = $notificationService->getNotificationSummary(auth()->id());
        $messages = $notificationService->generateNotificationMessages(auth()->id());

        return response()->json([
            'messages' => $messages,
            'summary' => $summary,
            'has_notifications' => count($messages) > 0,
        ]);
    }
}
