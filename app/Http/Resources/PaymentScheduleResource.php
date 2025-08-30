<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property $id
 * @property $scheduled_payment_id
 * @property $frequency
 * @property $interval
 * @property $day_of_month
 * @property $day_of_week
 * @property $max_occurrences
 * @property $occurrences_count
 * @property $auto_process
 * @property $create_transaction
 * @property $days_before_notification
 * @property $created_at
 * @property $updated_at
 */
class PaymentScheduleResource extends JsonResource
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
            'frequency' => $this->frequency,
            'interval' => $this->interval,
            'day_of_month' => $this->day_of_month,
            'day_of_week' => $this->day_of_week,
            'max_occurrences' => $this->max_occurrences,
            'occurrences_count' => $this->occurrences_count,
            'auto_process' => $this->auto_process,
            'create_transaction' => $this->create_transaction,
            'days_before_notification' => $this->days_before_notification,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
