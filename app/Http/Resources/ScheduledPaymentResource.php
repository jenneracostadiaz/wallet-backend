<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property $id
 * @property $name
 * @property $description
 * @property $payment_type
 * @property $status
 * @property $amount
 * @property $color
 * @property $icon
 * @property $start_date
 * @property $next_payment_date
 * @property $end_date
 * @property $account_id
 * @property $category_id
 * @property $user_id
 * @property $metadata
 * @property $order
 * @property $created_at
 * @property $updated_at
 * @property $account
 * @property $category
 * @property $paymentSchedule
 * @property $debtDetail
 * @property $paymentHistory
 */
class ScheduledPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'payment_type' => $this->payment_type,
            'status' => $this->status,
            'amount' => number_format($this->amount, 2, '.', ','),
            'color' => $this->color,
            'icon' => $this->icon,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'next_payment_date' => $this->next_payment_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'metadata' => $this->metadata,
            'order' => $this->order,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Relationships
            'account' => $this->whenLoaded('account', function () {
                return new AccountResource($this->account);
            }),
            'category' => $this->whenLoaded('category', function () {
                return new CategoryResource($this->category);
            }),
            'payment_schedule' => $this->whenLoaded('paymentSchedule', function () {
                return new PaymentScheduleResource($this->paymentSchedule);
            }),
            'debt_detail' => $this->whenLoaded('debtDetail', function () {
                return new DebtDetailResource($this->debtDetail);
            }),
            'payment_history' => $this->whenLoaded('paymentHistory', function () {
                return PaymentHistoryResource::collection($this->paymentHistory);
            }),
            'payment_history_count' => $this->when(
                $this->relationLoaded('paymentHistory'),
                $this->paymentHistory->count()
            ),
        ];
    }
}
