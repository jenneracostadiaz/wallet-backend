<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property $id
 * @property $name
 * @property $type
 * @property $balance
 * @property $color
 * @property $currency_id
 * @property $currency
 * @property $description
 * @property $order
 * @property $created_at
 * @property $updated_at
 */
class AccountResource extends JsonResource
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
            'type' => $this->type,
            'balance' => $this->balance,
            'color' => $this->color,
            'currency_id' => $this->currency_id,
            'currency' => [
                'id' => $this->currency->id,
                'code' => $this->currency->code,
                'name' => $this->currency->name,
                'symbol' => $this->currency->symbol,
            ],
            'description' => $this->description,
            'order' => $this->order,
        ];
    }
}
