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
 * @property $category_id
 * @property $category
 * @property $currency
 * @property $toAccount
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
            'category_id' => $this->category_id,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'type' => $this->category->type,
                'icon' => $this->category->icon,
            ],
            'account_id' => $this->account->id,
            'account' => [
                'id' => $this->account->id,
                'name' => $this->account->name,
                'type' => $this->account->type,
                'balance' => $this->account->balance,
                'color' => $this->account->color,
            ],
            'currency' => [
                'id' => $this->account->currency->id,
                'code' => $this->account->currency->code,
                'name' => $this->account->currency->name,
                'symbol' => $this->account->currency->symbol,
            ],
            'to_account_id' => $this->toAccount ? $this->toAccount->id : null,
            'to_account' => $this->type === 'transfer' && $this->toAccount ? [
                'id' => $this->toAccount->id,
                'name' => $this->toAccount->name,
                'type' => $this->toAccount->type,
                'balance' => $this->toAccount->balance,
                'color' => $this->toAccount->color,
            ] : null,
        ];
    }
}
