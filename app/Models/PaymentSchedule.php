<?php

namespace App\Models;

use App\Enums\PaymentFrequency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $scheduled_payment_id
 * @property PaymentFrequency|null $frequency
 * @property int $interval
 * @property int|null $day_of_month
 * @property string|null $day_of_week
 * @property int|null $max_occurrences
 * @property int $occurrences_count
 * @property bool $auto_process
 * @property int $days_before_notification
 * @property bool $create_transaction
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_payment_id',
        'frequency',
        'interval',
        'day_of_month',
        'day_of_week',
        'max_occurrences',
        'occurrences_count',
        'auto_process',
        'days_before_notification',
        'create_transaction',
    ];

    protected $casts = [
        'frequency' => PaymentFrequency::class,
        'interval' => 'integer',
        'day_of_month' => 'integer',
        'max_occurrences' => 'integer',
        'occurrences_count' => 'integer',
        'auto_process' => 'boolean',
        'days_before_notification' => 'integer',
        'create_transaction' => 'boolean',
    ];

    // Relaciones

    public function scheduledPayment(): BelongsTo
    {
        return $this->belongsTo(ScheduledPayment::class);
    }

    // Accessors

    public function getHasReachedMaxOccurrencesAttribute(): bool
    {
        return $this->max_occurrences && 
               $this->occurrences_count >= $this->max_occurrences;
    }

    public function getRemainingOccurrencesAttribute(): ?int
    {
        return $this->max_occurrences 
            ? max(0, $this->max_occurrences - $this->occurrences_count)
            : null;
    }

    // Métodos de utilidad

    public function calculateNextPaymentDate(Carbon $fromDate): ?Carbon
    {
        if (!$this->frequency) {
            return null;
        }

        $nextDate = $fromDate->copy();

        switch ($this->frequency) {
            case PaymentFrequency::Daily:
                $nextDate->addDays($this->interval);
                break;

            case PaymentFrequency::Weekly:
                $nextDate->addWeeks($this->interval);
                if ($this->day_of_week) {
                    $nextDate = $this->adjustToWeekday($nextDate, $this->day_of_week);
                }
                break;

            case PaymentFrequency::Biweekly:
                $nextDate->addDays(14 * $this->interval);
                break;

            case PaymentFrequency::Monthly:
                $nextDate->addMonths($this->interval);
                if ($this->day_of_month) {
                    $nextDate = $this->adjustToMonthDay($nextDate, $this->day_of_month);
                }
                break;

            case PaymentFrequency::Quarterly:
                $nextDate->addMonths(3 * $this->interval);
                if ($this->day_of_month) {
                    $nextDate = $this->adjustToMonthDay($nextDate, $this->day_of_month);
                }
                break;

            case PaymentFrequency::Yearly:
                $nextDate->addYears($this->interval);
                if ($this->day_of_month) {
                    $nextDate = $this->adjustToMonthDay($nextDate, $this->day_of_month);
                }
                break;
        }

        return $nextDate;
    }

    public function incrementOccurrences(): self
    {
        $this->increment('occurrences_count');
        return $this;
    }

    public function canProcessNextPayment(): bool
    {
        return !$this->has_reached_max_occurrences;
    }

    // Métodos privados

    private function adjustToWeekday(Carbon $date, string $weekday): Carbon
    {
        $weekdays = [
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
            'sunday' => Carbon::SUNDAY,
        ];

        if (!isset($weekdays[$weekday])) {
            return $date;
        }

        return $date->next($weekdays[$weekday]);
    }

    private function adjustToMonthDay(Carbon $date, int $dayOfMonth): Carbon
    {
        // Ajustar al día específico del mes, considerando meses con diferentes días
        $maxDayInMonth = $date->daysInMonth;
        $targetDay = min($dayOfMonth, $maxDayInMonth);
        
        return $date->day($targetDay);
    }
}
