<?php

namespace App\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property $user_id
 * @property int $id
 * @property $balance
 */
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'balance',
        'currency_id',
        'user_id',
        'description',
        'color',
        'order',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function setInitialBalance(float $amount): self
    {
        $this->balance = $amount;

        return $this;
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
