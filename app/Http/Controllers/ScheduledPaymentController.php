<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduledPaymentRequest;
use App\Http\Requests\UpdateScheduledPaymentRequest;
use App\Http\Resources\ScheduledPaymentResource;
use App\Models\ScheduledPayment;
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
            ...$request->validated(),
            'order' => $this->getNextOrder(),
        ]);

        // Load relationships for response
        $scheduledPayment->load(['account', 'category', 'paymentSchedule', 'debtDetail']);

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
}
