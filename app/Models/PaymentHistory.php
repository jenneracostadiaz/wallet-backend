<?php

namespace App\Models;

use App\Enums\PaymentHistoryStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $scheduled_payment_id
 * @property float $amount
 * @property float $planned_amount
 * @property PaymentHistoryStatus $status
 * @property Carbon $scheduled_date
 * @property Carbon|null $processed_date
 * @property Carbon|null $due_date
 * @property string|null $payment_method
 * @property string|null $reference_number
 * @property string|null $notes
 * @property string|null $failure_reason
 * @property int|null $transaction_id
 * @property array|null $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PaymentHistory extends Model
{
    use HasFactory;

    protected $table = 'payment_history'; // Especificar el nombre de la tabla

    protected $fillable = [
        'scheduled_payment_id',
        'amount',
        'planned_amount',
        'status',
        'scheduled_date',
        'processed_date',
        'due_date',
        'payment_method',
        'reference_number',
        'notes',
        'failure_reason',
        'transaction_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'planned_amount' => 'decimal:2',
        'status' => PaymentHistoryStatus::class,
        'scheduled_date' => 'datetime',
        'processed_date' => 'datetime',
        'due_date' => 'datetime',
        'metadata' => 'array',
    ];

    // Relaciones

    public function scheduledPayment(): BelongsTo
    {
        return $this->belongsTo(ScheduledPayment::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Scopes

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', PaymentHistoryStatus::Paid);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PaymentHistoryStatus::Pending);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', PaymentHistoryStatus::Failed);
    }

    public function scopeSkipped(Builder $query): Builder
    {
        return $query->where('status', PaymentHistoryStatus::Skipped);
    }

    public function scopePartial(Builder $query): Builder
    {
        return $query->where('status', PaymentHistoryStatus::Partial);
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->whereNotNull('processed_date');
    }

    public function scopeUnprocessed(Builder $query): Builder
    {
        return $query->whereNull('processed_date');
    }

    public function scopeInDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('scheduled_date', [$startDate, $endDate]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('scheduled_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeLastMonth(Builder $query): Builder
    {
        return $query->whereBetween('scheduled_date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ]);
    }

    public function scopeOrderedByDate(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('scheduled_date', $direction);
    }

    // Accessors

    public function getIsLateAttribute(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->status === PaymentHistoryStatus::Pending;
    }

    public function getDaysLateAttribute(): int
    {
        if (!$this->is_late) {
            return 0;
        }

        return (int) $this->due_date->diffInDays(now());
    }

    public function getAmountDifferenceAttribute(): float
    {
        return $this->amount - $this->planned_amount;
    }

    public function getIsPartialPaymentAttribute(): bool
    {
        return $this->amount < $this->planned_amount && $this->amount > 0;
    }

    public function getIsOverpaymentAttribute(): bool
    {
        return $this->amount > $this->planned_amount;
    }

    public function getProcessingDelayAttribute(): int
    {
        if (!$this->processed_date) {
            return 0;
        }

        return (int) $this->scheduled_date->diffInDays($this->processed_date);
    }

    // Métodos de utilidad

    public function markAsPaid(?float $amount = null, ?string $paymentMethod = null): self
    {
        $this->update([
            'status' => PaymentHistoryStatus::Paid,
            'amount' => $amount ?? $this->planned_amount,
            'processed_date' => now(),
            'payment_method' => $paymentMethod,
        ]);

        return $this;
    }

    public function markAsFailed(?string $reason = null): self
    {
        $this->update([
            'status' => PaymentHistoryStatus::Failed,
            'processed_date' => now(),
            'failure_reason' => $reason,
        ]);

        return $this;
    }

    public function markAsSkipped(?string $reason = null): self
    {
        $this->update([
            'status' => PaymentHistoryStatus::Skipped,
            'processed_date' => now(),
            'notes' => $reason,
        ]);

        return $this;
    }

    public function markAsPartial(float $amount, ?string $reason = null): self
    {
        $this->update([
            'status' => PaymentHistoryStatus::Partial,
            'amount' => $amount,
            'processed_date' => now(),
            'notes' => $reason,
        ]);

        return $this;
    }

    public function addNote(string $note): self
    {
        $currentNotes = $this->notes ? $this->notes . ' | ' : '';
        $this->update(['notes' => $currentNotes . $note]);

        return $this;
    }

    public function linkTransaction(Transaction $transaction): self
    {
        $this->update(['transaction_id' => $transaction->id]);
        return $this;
    }

    public function updateMetadata(array $data): self
    {
        $currentMetadata = $this->metadata ?? [];
        $this->update(['metadata' => array_merge($currentMetadata, $data)]);

        return $this;
    }
}
