<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property $id
 * @property $scheduled_payment_id
 * @property $amount
 * @property $planned_amount
 * @property $status
 * @property $scheduled_date
 * @property $processed_date
 * @property $transaction_id
 * @property $failure_reason
 * @property $notes
 * @property $created_at
 * @property $updated_at
 * @property $transaction
 */
class PaymentHistoryResource extends JsonResource
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
            'amount' => $this->amount,
            'planned_amount' => $this->planned_amount,
            'status' => $this->status,
            'scheduled_date' => $this->scheduled_date?->format('Y-m-d'),
            'processed_date' => $this->processed_date?->format('Y-m-d H:i:s'),
            'transaction_id' => $this->transaction_id,
            'failure_reason' => $this->failure_reason,
            'notes' => $this->notes,
            'amount_difference' => $this->when(
                $this->planned_amount,
                $this->amount - $this->planned_amount
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'transaction' => $this->whenLoaded('transaction', function () {
                return new TransactionResource($this->transaction);
            }),
        ];
    }
}
