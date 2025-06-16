<?php

namespace App\Models;

use App\Models\Builders\TransactionQueryBuilder;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property $amount
 * @property $description
 * @property $date
 * @property $type
 * @property $account_id
 * @property $category_id
 * @property $to_account_id
 * @property $user_id
 * @property $toAccount
 * @property $account
 *
 * @method static forUser(int|string|null $id)
 */
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'amount',
        'description',
        'date',
        'type',
        'account_id',
        'category_id',
        'to_account_id',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime',
    ];

    public function newEloquentBuilder($query): TransactionQueryBuilder
    {
        return new TransactionQueryBuilder($query);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
