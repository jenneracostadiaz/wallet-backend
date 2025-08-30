<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property $id
 * @property $scheduled_payment_id
 * @property $original_amount
 * @property $remaining_amount
 * @property $paid_amount
 * @property $total_installments
 * @property $paid_installments
 * @property $installment_amount
 * @property $interest_rate
 * @property $creditor
 * @property $reference_number
 * @property $due_date
 * @property $late_fee
 * @property $days_overdue
 * @property $created_at
 * @property $updated_at
 */
class DebtDetailResource extends JsonResource
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
            'scheduled_payment_id' => $this->scheduled_payment_id,
            'original_amount' => $this->original_amount,
            'remaining_amount' => $this->remaining_amount,
            'paid_amount' => $this->paid_amount,
            'total_installments' => $this->total_installments,
            'paid_installments' => $this->paid_installments,
            'installment_amount' => $this->installment_amount,
            'interest_rate' => $this->interest_rate,
            'creditor' => $this->creditor,
            'reference_number' => $this->reference_number,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'late_fee' => $this->late_fee,
            'days_overdue' => $this->days_overdue,
            'progress_percentage' => $this->original_amount > 0 
                ? round(($this->paid_amount / $this->original_amount) * 100, 2) 
                : 0,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
