<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property PaymentType $payment_type
 * @property PaymentStatus $status
 * @property float $amount
 * @property string $color
 * @property string|null $icon
 * @property Carbon $start_date
 * @property Carbon|null $next_payment_date
 * @property Carbon|null $end_date
 * @property int $account_id
 * @property int|null $category_id
 * @property int $user_id
 * @property array|null $metadata
 * @property int $order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ScheduledPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'payment_type',
        'status',
        'amount',
        'color',
        'icon',
        'start_date',
        'next_payment_date',
        'end_date',
        'account_id',
        'category_id',
        'user_id',
        'metadata',
        'order',
    ];

    protected $casts = [
        'payment_type' => PaymentType::class,
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'start_date' => 'datetime',
        'next_payment_date' => 'datetime',
        'end_date' => 'datetime',
        'metadata' => 'array',
        'order' => 'integer',
    ];

    // Relaciones

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function paymentSchedule(): HasOne
    {
        return $this->hasOne(PaymentSchedule::class);
    }

    public function debtDetail(): HasOne
    {
        return $this->hasOne(DebtDetail::class);
    }

    public function paymentHistory(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }

    // Scopes

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Active);
    }

    public function scopePaused(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Paused);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Completed);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Overdue);
    }

    public function scopeRecurring(Builder $query): Builder
    {
        return $query->where('payment_type', PaymentType::Recurring);
    }

    public function scopeDebts(Builder $query): Builder
    {
        return $query->where('payment_type', PaymentType::Debt);
    }

    public function scopeOneTime(Builder $query): Builder
    {
        return $query->where('payment_type', PaymentType::OneTime);
    }

    public function scopeUpcoming(Builder $query, int $days = 30): Builder
    {
        return $query->where('next_payment_date', '<=', now()->addDays($days))
                    ->where('next_payment_date', '>=', now())
                    ->whereIn('status', [PaymentStatus::Active, PaymentStatus::Overdue]);
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('next_payment_date', today())
                    ->where('status', PaymentStatus::Active);
    }

    public function scopeOrderedByNext(Builder $query): Builder
    {
        return $query->orderBy('next_payment_date', 'asc');
    }

    public function scopeOrderedByAmount(Builder $query): Builder
    {
        return $query->orderBy('amount', 'desc');
    }

    // Accessors & Mutators

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->next_payment_date) {
            return false;
        }

        return $this->next_payment_date->isPast() && 
               $this->status === PaymentStatus::Active;
    }

    public function getDaysUntilNextPaymentAttribute(): int
    {
        if (!$this->next_payment_date) {
            return 0;
        }

        return (int) now()->diffInDays($this->next_payment_date, false);
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->paymentHistory()
                           ->where('status', \App\Enums\PaymentHistoryStatus::Paid)
                           ->sum('amount');
    }

    public function getCompletionPercentageAttribute(): int
    {
        if ($this->payment_type !== PaymentType::Debt) {
            return 0;
        }

        $debtDetail = $this->debtDetail;
        if (!$debtDetail || $debtDetail->original_amount == 0) {
            return 0;
        }

        return (int) (($debtDetail->paid_amount / $debtDetail->original_amount) * 100);
    }

    // Métodos de utilidad

    public function markAsCompleted(): self
    {
        $this->update(['status' => PaymentStatus::Completed]);
        return $this;
    }

    public function markAsOverdue(): self
    {
        $this->update(['status' => PaymentStatus::Overdue]);
        return $this;
    }

    public function pause(): self
    {
        $this->update(['status' => PaymentStatus::Paused]);
        return $this;
    }

    public function resume(): self
    {
        $this->update(['status' => PaymentStatus::Active]);
        return $this;
    }

    public function cancel(): self
    {
        $this->update(['status' => PaymentStatus::Cancelled]);
        return $this;
    }

    public function updateNextPaymentDate(?Carbon $date = null): self
    {
        $this->update(['next_payment_date' => $date]);
        return $this;
    }

    public function canBeProcessed(): bool
    {
        return $this->status === PaymentStatus::Active && 
               $this->next_payment_date && 
               $this->next_payment_date->lte(now());
    }
}
