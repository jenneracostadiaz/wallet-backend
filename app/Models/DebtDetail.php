<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $scheduled_payment_id
 * @property float $original_amount
 * @property float $remaining_amount
 * @property float $paid_amount
 * @property int $total_installments
 * @property int $paid_installments
 * @property float|null $installment_amount
 * @property float|null $interest_rate
 * @property string|null $creditor
 * @property string|null $reference_number
 * @property Carbon|null $due_date
 * @property float $late_fee
 * @property int $days_overdue
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DebtDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_payment_id',
        'original_amount',
        'remaining_amount',
        'paid_amount',
        'total_installments',
        'paid_installments',
        'installment_amount',
        'interest_rate',
        'creditor',
        'reference_number',
        'due_date',
        'late_fee',
        'days_overdue',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'due_date' => 'datetime',
        'total_installments' => 'integer',
        'paid_installments' => 'integer',
        'days_overdue' => 'integer',
    ];

    // Relaciones

    public function scheduledPayment(): BelongsTo
    {
        return $this->belongsTo(ScheduledPayment::class);
    }

    // Scopes

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
                    ->where('remaining_amount', '>', 0);
    }

    public function scopePartiallyPaid(Builder $query): Builder
    {
        return $query->where('paid_amount', '>', 0)
                    ->where('remaining_amount', '>', 0);
    }

    public function scopeFullyPaid(Builder $query): Builder
    {
        return $query->where('remaining_amount', '<=', 0);
    }

    // Accessors

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->remaining_amount > 0;
    }

    public function getPaymentProgressAttribute(): float
    {
        if ($this->original_amount == 0) {
            return 0;
        }

        return ($this->paid_amount / $this->original_amount) * 100;
    }

    public function getInstallmentProgressAttribute(): float
    {
        if ($this->total_installments == 0) {
            return 0;
        }

        return ($this->paid_installments / $this->total_installments) * 100;
    }

    public function getRemainingInstallmentsAttribute(): int
    {
        return max(0, $this->total_installments - $this->paid_installments);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function getDaysUntilDueAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        return (int) now()->diffInDays($this->due_date, false);
    }

    public function getMonthlyInterestAttribute(): float
    {
        if (!$this->interest_rate) {
            return 0;
        }

        return ($this->remaining_amount * $this->interest_rate) / 100 / 12;
    }

    // Métodos de utilidad

    public function makePayment(float $amount): self
    {
        $this->paid_amount += $amount;
        $this->remaining_amount = max(0, $this->remaining_amount - $amount);
        
        // Si se completa una cuota
        if ($this->installment_amount && $amount >= $this->installment_amount) {
            $this->paid_installments += (int) floor($amount / $this->installment_amount);
        }

        $this->save();

        return $this;
    }

    public function addLateFee(float $fee): self
    {
        $this->late_fee += $fee;
        $this->remaining_amount += $fee;
        $this->save();

        return $this;
    }

    public function updateOverdueDays(): self
    {
        if ($this->due_date && $this->due_date->isPast()) {
            $this->days_overdue = (int) $this->due_date->diffInDays(now());
            $this->save();
        }

        return $this;
    }

    public function calculateNextInstallmentDueDate(): ?Carbon
    {
        if (!$this->due_date) {
            return null;
        }

        $installmentsPaid = $this->paid_installments;
        $totalInstallments = $this->total_installments;

        if ($installmentsPaid >= $totalInstallments) {
            return null;
        }

        // Asumiendo pagos mensuales por defecto
        return $this->due_date->copy()->addMonths($installmentsPaid + 1);
    }

    public function recalculateInstallmentAmount(): self
    {
        if ($this->remaining_installments > 0) {
            $this->installment_amount = $this->remaining_amount / $this->remaining_installments;
            $this->save();
        }

        return $this;
    }
}
