<?php

namespace Database\Factories;

use App\Models\DebtDetail;
use App\Models\ScheduledPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DebtDetail>
 */
class DebtDetailFactory extends Factory
{
    protected $model = DebtDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalAmount = $this->faker->randomFloat(2, 1000, 50000);
        $paidAmount = $this->faker->randomFloat(2, 0, $originalAmount * 0.8);
        $remainingAmount = $originalAmount - $paidAmount;
        
        $totalInstallments = $this->faker->randomElement([1, 3, 6, 12, 18, 24, 36, 48]);
        $paidInstallments = $this->faker->numberBetween(0, min($totalInstallments - 1, 10));
        $installmentAmount = $totalInstallments > 1 ? $originalAmount / $totalInstallments : $originalAmount;

        return [
            'scheduled_payment_id' => ScheduledPayment::factory(),
            'original_amount' => $originalAmount,
            'remaining_amount' => $remainingAmount,
            'paid_amount' => $paidAmount,
            'total_installments' => $totalInstallments,
            'paid_installments' => $paidInstallments,
            'installment_amount' => $installmentAmount,
            'interest_rate' => $this->faker->optional(0.7)->randomFloat(2, 0.5, 15.0), // 0.5% - 15% anual
            'creditor' => $this->getCreditorName(),
            'reference_number' => $this->faker->optional(0.8)->bothify('??##-####-####'),
            'due_date' => $this->faker->dateTimeBetween('-1 month', '+6 months'),
            'late_fee' => $this->faker->randomFloat(2, 0, 200),
            'days_overdue' => $this->faker->numberBetween(0, 90),
        ];
    }

    /**
     * Estados específicos
     */
    public function newDebt(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => 0,
            'remaining_amount' => $attributes['original_amount'],
            'paid_installments' => 0,
            'late_fee' => 0,
            'days_overdue' => 0,
        ]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(function (array $attributes) {
            $originalAmount = $attributes['original_amount'];
            $paidAmount = $this->faker->randomFloat(2, $originalAmount * 0.1, $originalAmount * 0.7);
            $paidInstallments = $this->faker->numberBetween(1, $attributes['total_installments'] - 1);
            
            return [
                'paid_amount' => $paidAmount,
                'remaining_amount' => $originalAmount - $paidAmount,
                'paid_installments' => $paidInstallments,
            ];
        });
    }

    public function fullyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['original_amount'],
            'remaining_amount' => 0,
            'paid_installments' => $attributes['total_installments'],
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-3 months', '-1 day'),
            'late_fee' => $this->faker->randomFloat(2, 50, 500),
            'days_overdue' => $this->faker->numberBetween(1, 90),
        ]);
    }

    public function withInterest(): static
    {
        return $this->state(fn (array $attributes) => [
            'interest_rate' => $this->faker->randomFloat(2, 1.0, 25.0),
        ]);
    }

    public function singlePayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_installments' => 1,
            'installment_amount' => $attributes['original_amount'],
        ]);
    }

    public function installments(): static
    {
        return $this->state(function (array $attributes) {
            $totalInstallments = $this->faker->randomElement([6, 12, 18, 24, 36]);
            return [
                'total_installments' => $totalInstallments,
                'installment_amount' => $attributes['original_amount'] / $totalInstallments,
            ];
        });
    }

    /**
     * Tipos de deuda específicos
     */
    public function personalLoan(): static
    {
        return $this->state(fn (array $attributes) => [
            'original_amount' => $this->faker->randomFloat(2, 5000, 80000),
            'creditor' => $this->faker->randomElement(['BCP', 'Interbank', 'BBVA', 'Scotiabank', 'BanBif']),
            'interest_rate' => $this->faker->randomFloat(2, 12.0, 35.0),
            'total_installments' => $this->faker->randomElement([12, 18, 24, 36, 48]),
            'reference_number' => 'PR-' . $this->faker->numerify('########'),
        ]);
    }

    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'original_amount' => $this->faker->randomFloat(2, 500, 15000),
            'creditor' => $this->faker->randomElement(['Visa BCP', 'MasterCard Interbank', 'American Express', 'Diners Club']),
            'interest_rate' => $this->faker->randomFloat(2, 18.0, 45.0),
            'total_installments' => $this->faker->randomElement([1, 3, 6, 12]),
            'reference_number' => 'TC-' . $this->faker->numerify('####-####-####-####'),
        ]);
    }

    public function mortgage(): static
    {
        return $this->state(fn (array $attributes) => [
            'original_amount' => $this->faker->randomFloat(2, 80000, 500000),
            'creditor' => $this->faker->randomElement(['Mi Vivienda', 'BCP Hipotecario', 'Interbank Casa', 'BBVA Vivienda']),
            'interest_rate' => $this->faker->randomFloat(2, 6.5, 12.0),
            'total_installments' => $this->faker->randomElement([120, 180, 240, 300, 360]), // 10-30 años
            'reference_number' => 'HIP-' . $this->faker->numerify('########'),
        ]);
    }

    public function carLoan(): static
    {
        return $this->state(fn (array $attributes) => [
            'original_amount' => $this->faker->randomFloat(2, 15000, 120000),
            'creditor' => $this->faker->randomElement(['Toyota Financial', 'Ford Credit', 'Nissan Finance', 'Banco Auto']),
            'interest_rate' => $this->faker->randomFloat(2, 8.0, 18.0),
            'total_installments' => $this->faker->randomElement([36, 48, 60, 72]),
            'reference_number' => 'AUTO-' . $this->faker->numerify('########'),
        ]);
    }

    public function personalDebt(): static
    {
        return $this->state(fn (array $attributes) => [
            'original_amount' => $this->faker->randomFloat(2, 200, 10000),
            'creditor' => $this->faker->randomElement(['Juan Pérez', 'María García', 'Carlos López', 'Ana Rodríguez', 'Familia']),
            'interest_rate' => $this->faker->optional(0.3)->randomFloat(2, 0, 5.0), // Interés bajo o sin interés
            'total_installments' => $this->faker->randomElement([1, 2, 3, 6, 12]),
            'reference_number' => null, // Deudas personales sin referencia
        ]);
    }

    /**
     * Método auxiliar para nombres de acreedores
     */
    private function getCreditorName(): string
    {
        return $this->faker->randomElement([
            // Bancos
            'Banco de Crédito BCP',
            'Interbank',
            'BBVA Continental',
            'Scotiabank Perú',
            'BanBif',
            'Banco Pichincha',
            
            // Financieras
            'Crediscotia',
            'CrediCorp Capital',
            'Financiera Confianza',
            'Compartamos Financiera',
            'Mitsui Auto Finance',
            
            // Tiendas y servicios
            'Saga Falabella',
            'Ripley',
            'Hiraoka',
            'Elektra',
            'Curacao',
            
            // Personas
            'Juan Carlos Mendoza',
            'María Elena García',
            'Carlos Roberto López',
            'Ana Patricia Silva',
            'José Luis Herrera',
            
            // Otros
            'Cooperativa de Ahorro',
            'Caja Municipal',
            'Prestamista Particular',
        ]);
    }
}
