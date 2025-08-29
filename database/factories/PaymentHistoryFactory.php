<?php

namespace Database\Factories;

use App\Enums\PaymentHistoryStatus;
use App\Models\PaymentHistory;
use App\Models\ScheduledPayment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentHistory>
 */
class PaymentHistoryFactory extends Factory
{
    protected $model = PaymentHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plannedAmount = $this->faker->randomFloat(2, 50, 2000);
        $status = $this->faker->randomElement(PaymentHistoryStatus::cases());
        $scheduledDate = $this->faker->dateTimeBetween('-6 months', '+1 month');
        
        return [
            'scheduled_payment_id' => ScheduledPayment::factory(),
            'amount' => $this->getActualAmount($plannedAmount, $status),
            'planned_amount' => $plannedAmount,
            'status' => $status,
            'scheduled_date' => $scheduledDate,
            'processed_date' => $this->getProcessedDate($status, $scheduledDate),
            'due_date' => $this->faker->optional(0.6)->dateTimeBetween($scheduledDate, '+15 days'),
            'payment_method' => $this->getPaymentMethod($status),
            'reference_number' => $this->getReferenceNumber($status),
            'notes' => $this->getNotes($status),
            'failure_reason' => $this->getFailureReason($status),
            'transaction_id' => $status === PaymentHistoryStatus::Paid ? Transaction::factory() : null,
            'metadata' => $this->getMetadata($status),
        ];
    }

    /**
     * Estados específicos
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentHistoryStatus::Paid,
            'processed_date' => $this->faker->dateTimeBetween($attributes['scheduled_date'], 'now'),
            'payment_method' => $this->faker->randomElement(['transferencia', 'tarjeta_debito', 'tarjeta_credito', 'efectivo']),
            'reference_number' => $this->faker->numerify('PAY-########'),
            'transaction_id' => Transaction::factory(),
            'failure_reason' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentHistoryStatus::Pending,
            'processed_date' => null,
            'payment_method' => null,
            'reference_number' => null,
            'transaction_id' => null,
            'failure_reason' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentHistoryStatus::Failed,
            'amount' => 0,
            'processed_date' => $this->faker->dateTimeBetween($attributes['scheduled_date'], 'now'),
            'payment_method' => null,
            'reference_number' => null,
            'transaction_id' => null,
            'failure_reason' => $this->faker->randomElement([
                'Saldo insuficiente en la cuenta',
                'Cuenta bloqueada temporalmente',
                'Error en el sistema bancario',
                'Tarjeta vencida',
                'Límite de transacciones excedido',
                'Cuenta de destino no válida',
                'Error de conectividad',
            ]),
        ]);
    }

    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentHistoryStatus::Skipped,
            'amount' => 0,
            'processed_date' => $this->faker->dateTimeBetween($attributes['scheduled_date'], 'now'),
            'payment_method' => null,
            'reference_number' => null,
            'transaction_id' => null,
            'failure_reason' => null,
            'notes' => $this->faker->randomElement([
                'Omitido por falta de fondos',
                'Pago pospuesto por el usuario',
                'Servicio suspendido temporalmente',
                'Cambio en las condiciones del pago',
            ]),
        ]);
    }

    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            $plannedAmount = $attributes['planned_amount'];
            $partialAmount = $this->faker->randomFloat(2, $plannedAmount * 0.2, $plannedAmount * 0.8);
            
            return [
                'status' => PaymentHistoryStatus::Partial,
                'amount' => $partialAmount,
                'processed_date' => $this->faker->dateTimeBetween($attributes['scheduled_date'], 'now'),
                'payment_method' => $this->faker->randomElement(['transferencia', 'tarjeta_debito']),
                'reference_number' => $this->faker->numerify('PART-########'),
                'transaction_id' => Transaction::factory(),
                'failure_reason' => null,
                'notes' => 'Pago parcial - pendiente completar saldo restante',
            ];
        });
    }

    /**
     * Rangos de fechas
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_date' => $this->faker->dateTimeBetween('first day of this month', 'last day of this month'),
        ]);
    }

    public function lastMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_date' => $this->faker->dateTimeBetween('first day of last month', 'last day of last month'),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => PaymentHistoryStatus::Pending,
            'processed_date' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'due_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'status' => PaymentHistoryStatus::Pending,
            'processed_date' => null,
        ]);
    }

    public function withTransaction(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentHistoryStatus::Paid,
            'transaction_id' => Transaction::factory(),
        ]);
    }

    /**
     * Métodos auxiliares
     */
    private function getActualAmount(float $plannedAmount, PaymentHistoryStatus $status): float
    {
        return match ($status) {
            PaymentHistoryStatus::Paid => $this->faker->optional(0.9)->passthrough($plannedAmount) ?? 
                                         $this->faker->randomFloat(2, $plannedAmount * 0.95, $plannedAmount * 1.05),
            PaymentHistoryStatus::Partial => $this->faker->randomFloat(2, $plannedAmount * 0.1, $plannedAmount * 0.8),
            PaymentHistoryStatus::Failed, PaymentHistoryStatus::Skipped => 0,
            PaymentHistoryStatus::Pending => $plannedAmount,
        };
    }

    private function getProcessedDate(PaymentHistoryStatus $status, $scheduledDate): ?\DateTime
    {
        if ($status === PaymentHistoryStatus::Pending) {
            return null;
        }

        $scheduledCarbon = \Carbon\Carbon::instance($scheduledDate);
        
        return match ($status) {
            PaymentHistoryStatus::Paid => $this->faker->dateTimeBetween($scheduledDate, $scheduledCarbon->copy()->addDays(5)),
            PaymentHistoryStatus::Failed => $this->faker->dateTimeBetween($scheduledDate, $scheduledCarbon->copy()->addDays(2)),
            PaymentHistoryStatus::Skipped => $this->faker->dateTimeBetween($scheduledDate, $scheduledCarbon->copy()->addDays(1)),
            PaymentHistoryStatus::Partial => $this->faker->dateTimeBetween($scheduledDate, $scheduledCarbon->copy()->addDays(3)),
            default => null,
        };
    }

    private function getPaymentMethod(PaymentHistoryStatus $status): ?string
    {
        if (in_array($status, [PaymentHistoryStatus::Failed, PaymentHistoryStatus::Skipped, PaymentHistoryStatus::Pending])) {
            return null;
        }

        return $this->faker->randomElement([
            'transferencia_bancaria',
            'tarjeta_debito',
            'tarjeta_credito',
            'billetera_digital',
            'efectivo',
            'deposito_banco',
            'pago_online',
        ]);
    }

    private function getReferenceNumber(PaymentHistoryStatus $status): ?string
    {
        if (in_array($status, [PaymentHistoryStatus::Failed, PaymentHistoryStatus::Skipped, PaymentHistoryStatus::Pending])) {
            return null;
        }

        $prefixes = [
            PaymentHistoryStatus::Paid => 'PAY',
            PaymentHistoryStatus::Partial => 'PART',
        ];

        $prefix = $prefixes[$status] ?? 'TXN';
        
        return $prefix . '-' . $this->faker->numerify('########');
    }

    private function getNotes(PaymentHistoryStatus $status): ?string
    {
        $notes = [
            PaymentHistoryStatus::Paid => [
                'Pago procesado exitosamente',
                'Transacción completada sin problemas',
                'Débito automático ejecutado',
                null, null, null, // Más probabilidad de null
            ],
            PaymentHistoryStatus::Pending => [
                'Esperando procesamiento',
                'En cola de pagos',
                null, null,
            ],
            PaymentHistoryStatus::Failed => [
                null, null,
            ],
            PaymentHistoryStatus::Skipped => [
                'Omitido por el usuario',
                'Pospuesto hasta próximo periodo',
                'Suspendido temporalmente',
            ],
            PaymentHistoryStatus::Partial => [
                'Pago parcial realizado',
                'Pendiente completar saldo restante',
                'Abono parcial aplicado',
            ],
        ];

        return $this->faker->optional(0.3)->randomElement($notes[$status] ?? [null]);
    }

    private function getFailureReason(PaymentHistoryStatus $status): ?string
    {
        if ($status !== PaymentHistoryStatus::Failed) {
            return null;
        }

        return $this->faker->randomElement([
            'Fondos insuficientes en la cuenta',
            'Cuenta de origen bloqueada',
            'Error en el sistema de pagos',
            'Tarjeta expirada',
            'Límite diario excedido',
            'Cuenta de destino inválida',
            'Timeout en la transacción',
            'Error de conectividad con el banco',
            'Transacción rechazada por el emisor',
            'Cuenta cerrada o suspendida',
        ]);
    }

    private function getMetadata(PaymentHistoryStatus $status): ?array
    {
        if ($this->faker->boolean(70)) { // 70% chance of no metadata
            return null;
        }

        $metadata = [
            'attempt_number' => $this->faker->numberBetween(1, 3),
            'processing_time_ms' => $this->faker->numberBetween(500, 5000),
        ];

        if ($status === PaymentHistoryStatus::Paid) {
            $metadata['bank_response_code'] = '00';
            $metadata['authorization_code'] = $this->faker->numerify('######');
        }

        if ($status === PaymentHistoryStatus::Failed) {
            $metadata['error_code'] = $this->faker->randomElement(['E001', 'E002', 'E003', 'E051', 'E091']);
            $metadata['retry_count'] = $this->faker->numberBetween(1, 3);
        }

        return $metadata;
    }
}
