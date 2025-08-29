<?php

namespace Database\Factories;

use App\Enums\PaymentFrequency;
use App\Models\PaymentSchedule;
use App\Models\ScheduledPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentSchedule>
 */
class PaymentScheduleFactory extends Factory
{
    protected $model = PaymentSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $frequency = $this->faker->randomElement(PaymentFrequency::cases());
        
        return [
            'scheduled_payment_id' => ScheduledPayment::factory(),
            'frequency' => $frequency,
            'interval' => $this->getInterval($frequency),
            'day_of_month' => $this->getDayOfMonth($frequency),
            'day_of_week' => $this->getDayOfWeek($frequency),
            'max_occurrences' => $this->faker->optional(0.3)->numberBetween(6, 60),
            'occurrences_count' => $this->faker->numberBetween(0, 12),
            'auto_process' => $this->faker->boolean(85), // 85% auto process
            'days_before_notification' => $this->faker->randomElement([1, 2, 3, 5, 7]),
            'create_transaction' => $this->faker->boolean(90), // 90% create transaction
        ];
    }

    /**
     * Frecuencias específicas
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => PaymentFrequency::Monthly,
            'interval' => 1,
            'day_of_month' => $this->faker->numberBetween(1, 28),
            'day_of_week' => null,
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => PaymentFrequency::Weekly,
            'interval' => 1,
            'day_of_month' => null,
            'day_of_week' => $this->faker->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        ]);
    }

    public function biweekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => PaymentFrequency::Biweekly,
            'interval' => 1,
            'day_of_month' => null,
            'day_of_week' => null,
        ]);
    }

    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => PaymentFrequency::Quarterly,
            'interval' => 1,
            'day_of_month' => $this->faker->numberBetween(1, 28),
            'day_of_week' => null,
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => PaymentFrequency::Yearly,
            'interval' => 1,
            'day_of_month' => $this->faker->numberBetween(1, 28),
            'day_of_week' => null,
        ]);
    }

    public function withLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_occurrences' => $this->faker->numberBetween(12, 48),
        ]);
    }

    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_occurrences' => null,
        ]);
    }

    public function autoProcess(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_process' => true,
            'create_transaction' => true,
        ]);
    }

    public function manualProcess(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_process' => false,
            'create_transaction' => false,
        ]);
    }

    /**
     * Métodos auxiliares
     */
    private function getInterval(PaymentFrequency $frequency): int
    {
        return match ($frequency) {
            PaymentFrequency::Daily => $this->faker->randomElement([1, 2, 3, 7]), // Cada 1-7 días
            PaymentFrequency::Weekly => $this->faker->randomElement([1, 2]), // Cada 1-2 semanas
            PaymentFrequency::Biweekly => 1, // Siempre cada 15 días
            PaymentFrequency::Monthly => $this->faker->randomElement([1, 2, 3, 6]), // Cada 1-6 meses
            PaymentFrequency::Quarterly => $this->faker->randomElement([1, 2]), // Cada 1-2 trimestres
            PaymentFrequency::Yearly => 1, // Siempre anual
        };
    }

    private function getDayOfMonth(PaymentFrequency $frequency): ?int
    {
        return match ($frequency) {
            PaymentFrequency::Monthly,
            PaymentFrequency::Quarterly,
            PaymentFrequency::Yearly => $this->faker->numberBetween(1, 28), // Días seguros
            default => null,
        };
    }

    private function getDayOfWeek(PaymentFrequency $frequency): ?string
    {
        if ($frequency === PaymentFrequency::Weekly) {
            return $this->faker->randomElement([
                'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
            ]);
        }
        
        return null;
    }
}
