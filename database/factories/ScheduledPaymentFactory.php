<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Account;
use App\Models\Category;
use App\Models\ScheduledPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduledPayment>
 */
class ScheduledPaymentFactory extends Factory
{
    protected $model = ScheduledPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentType = $this->faker->randomElement(PaymentType::cases());
        
        return [
            'name' => $this->getPaymentName($paymentType),
            'description' => $this->getPaymentDescription($paymentType),
            'payment_type' => $paymentType,
            'status' => $this->faker->randomElement([
                PaymentStatus::Active,
                PaymentStatus::Active, // Más probabilidad de activos
                PaymentStatus::Active,
                PaymentStatus::Paused,
                PaymentStatus::Completed,
            ]),
            'amount' => $this->getPaymentAmount($paymentType),
            'color' => $this->faker->hexColor(),
            'icon' => $this->getPaymentIcon($paymentType),
            'start_date' => $startDate = $this->faker->dateTimeBetween('-6 months', 'now'),
            'next_payment_date' => $this->getNextPaymentDate($paymentType, $startDate),
            'end_date' => $paymentType === PaymentType::Recurring ? null : $this->faker->optional(0.3)->dateTimeBetween('+1 month', '+2 years'),
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'category_id' => Category::factory(),
            'metadata' => $this->faker->optional(0.4)->randomElement([
                ['notes' => 'Creado automáticamente'],
                ['priority' => 'high'],
                ['tags' => ['importante', 'recurrente']],
            ]),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Estados específicos
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Active,
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Paused,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Completed,
            'next_payment_date' => null, // Completed payments shouldn't have next payment dates
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Overdue,
            'next_payment_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Tipos específicos
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => PaymentType::Recurring,
            'name' => $this->faker->randomElement([
                'Suscripción Netflix', 'Spotify Premium', 'Amazon Prime',
                'Plan Movistar', 'Internet Claro', 'Disney Plus',
                'YouTube Premium', 'Office 365', 'Adobe Creative',
                'Gym Bodytech', 'Seguro Rimac', 'Mantenimiento Auto'
            ]),
            'amount' => $this->faker->randomFloat(2, 19.90, 199.90),
        ]);
    }

    public function debt(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => PaymentType::Debt,
            'name' => $this->faker->randomElement([
                'Préstamo Personal BCP', 'Tarjeta Interbank', 'Cuotas Laptop',
                'Financiamiento Auto', 'Préstamo Vivienda', 'Cuotas Celular',
                'Deuda Familiar', 'Préstamo Negocio', 'Cuotas Electrodomésticos'
            ]),
            'amount' => $this->faker->randomFloat(2, 250.00, 2500.00),
        ]);
    }

    public function oneTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => PaymentType::OneTime,
            'name' => $this->faker->randomElement([
                'Recibo Luz Enel', 'Factura Agua Sedapal', 'Seguro Vehicular',
                'Impuesto Predial', 'Pago Universidad', 'Consulta Médica',
                'Renovación Licencia', 'Mantenimiento Casa', 'Evento Especial'
            ]),
            'amount' => $this->faker->randomFloat(2, 45.00, 850.00),
            'next_payment_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'end_date' => $this->faker->dateTimeBetween('+1 day', '+3 months'), // Ensure end_date is set for one-time payments
        ]);
    }

    /**
     * Métodos auxiliares
     */
    private function getPaymentName(PaymentType $type): string
    {
        return match ($type) {
            PaymentType::Recurring => $this->faker->randomElement([
                'Netflix Premium', 'Spotify Familiar', 'Amazon Prime Video',
                'Plan Postpago Claro', 'Internet Fibra Movistar', 'Disney Plus',
                'YouTube Music', 'Microsoft 365', 'Adobe Photoshop',
                'Gimnasio Bodytech', 'Seguro EPS Rimac', 'Mantenimiento Vehicular'
            ]),
            PaymentType::Debt => $this->faker->randomElement([
                'Préstamo Personal BCP', 'Tarjeta de Crédito Interbank', 'Cuotas MacBook Pro',
                'Financiamiento Toyota', 'Hipoteca Mi Vivienda', 'Cuotas iPhone',
                'Deuda con Hermano', 'Capital de Trabajo', 'Cuotas Refrigeradora Samsung'
            ]),
            PaymentType::OneTime => $this->faker->randomElement([
                'Recibo Enel Este Mes', 'Factura Agua Sedapal', 'Seguro Vehicular Anual',
                'Impuesto Predial 2025', 'Matrícula Universidad', 'Chequeo Médico',
                'Renovación Brevete', 'Reparación Techo', 'Cumpleaños María'
            ]),
        };
    }

    private function getPaymentDescription(PaymentType $type): ?string
    {
        return match ($type) {
            PaymentType::Recurring => $this->faker->optional(0.7)->randomElement([
                'Suscripción mensual renovable',
                'Servicio con descuento automático',
                'Plan familiar compartido',
                'Membresía premium con beneficios'
            ]),
            PaymentType::Debt => $this->faker->optional(0.8)->randomElement([
                'Cuotas mensuales fijas',
                'Préstamo con interés variable',
                'Financiamiento a 24 meses',
                'Deuda consolidada'
            ]),
            PaymentType::OneTime => $this->faker->optional(0.5)->randomElement([
                'Pago único programado',
                'Factura de servicios',
                'Pago anual obligatorio',
                'Gasto extraordinario'
            ]),
        };
    }

    private function getPaymentAmount(PaymentType $type): float
    {
        return match ($type) {
            PaymentType::Recurring => $this->faker->randomFloat(2, 19.90, 299.90),
            PaymentType::Debt => $this->faker->randomFloat(2, 150.00, 3500.00),
            PaymentType::OneTime => $this->faker->randomFloat(2, 35.00, 1200.00),
        };
    }

    private function getPaymentIcon(PaymentType $type): string
    {
        return match ($type) {
            PaymentType::Recurring => $this->faker->randomElement(['🔄', '📺', '🎵', '📱', '💻', '🏋️']),
            PaymentType::Debt => $this->faker->randomElement(['💳', '🏦', '💰', '🏠', '🚗']),
            PaymentType::OneTime => $this->faker->randomElement(['📅', '💡', '🚰', '🏥', '📚']),
        };
    }

    private function getNextPaymentDate(PaymentType $type, $startDate): ?\DateTime
    {
        $startCarbon = \Carbon\Carbon::instance($startDate);
        
        return match ($type) {
            PaymentType::Recurring => $startCarbon->copy()->addMonth(),
            PaymentType::Debt => $startCarbon->copy()->addMonth(),
            PaymentType::OneTime => $this->faker->dateTimeBetween('now', '+3 months'),
        };
    }
}
