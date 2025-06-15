<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property $id
 * @property $type
 * @property $amount
 * @property $date
 * @property $description
 * @property $account
 * @property $category
 * @property $currency
 */
class TransactionResource extends JsonResource
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
            'type' => $this->type,
            'amount' => $this->amount,
            'date' => $this->date->toDateTimeString(),
            'description' => $this->description,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'type' => $this->category->type,
                'icon' => $this->category->icon,
            ],
            'account' => [
                'id' => $this->account->id,
                'name' => $this->account->name,
                'type' => $this->account->type,
                'balance' => $this->account->balance,
            ],
            'currency' => [
                'id' => $this->account->currency->id,
                'code' => $this->account->currency->code,
                'name' => $this->account->currency->name,
                'symbol' => $this->account->currency->symbol,
            ],
        ];
    }
}
