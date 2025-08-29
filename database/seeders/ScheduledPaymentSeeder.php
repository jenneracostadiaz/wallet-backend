<?php

namespace Database\Seeders;

use App\Enums\PaymentFrequency;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Account;
use App\Models\Category;
use App\Models\DebtDetail;
use App\Models\PaymentHistory;
use App\Models\PaymentSchedule;
use App\Models\ScheduledPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduledPaymentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan usuarios y cuentas
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('No hay usuarios en la base de datos. Ejecuta primero UserSeeder.');
            return;
        }

        $this->command->info('Creando pagos programados para cada usuario...');

        foreach ($users as $user) {
            $this->createScheduledPaymentsForUser($user);
        }

        $this->command->info('¡Pagos programados creados exitosamente!');
    }

    private function createScheduledPaymentsForUser(User $user): void
    {
        // Obtener cuentas y categorías del usuario
        $userAccounts = $user->accounts;
        $userCategories = $user->categories;

        if ($userAccounts->isEmpty() || $userCategories->isEmpty()) {
            $this->command->warn("Usuario {$user->name} no tiene cuentas o categorías. Saltando...");
            return;
        }

        $this->command->info("Creando pagos para usuario: {$user->name}");

        // Crear mix balanceado de pagos (40% recurrentes, 35% deudas, 25% únicos)
        $this->createRecurringPayments($user, $userAccounts, $userCategories, 6); // 40%
        $this->createDebtPayments($user, $userAccounts, $userCategories, 5);      // 35%
        $this->createOneTimePayments($user, $userAccounts, $userCategories, 4);   // 25%
    }

    private function createRecurringPayments(User $user, $accounts, $categories, int $count): void
    {
        $recurringServices = [
            // Suscripciones de entretenimiento
            ['name' => 'Netflix Premium', 'amount' => 45.90, 'frequency' => PaymentFrequency::Monthly, 'icon' => '📺'],
            ['name' => 'Spotify Familiar', 'amount' => 19.90, 'frequency' => PaymentFrequency::Monthly, 'icon' => '🎵'],
            ['name' => 'Disney Plus', 'amount' => 25.90, 'frequency' => PaymentFrequency::Monthly, 'icon' => '🏰'],
            ['name' => 'Amazon Prime', 'amount' => 35.90, 'frequency' => PaymentFrequency::Monthly, 'icon' => '📦'],
            ['name' => 'YouTube Premium', 'amount' => 18.90, 'frequency' => PaymentFrequency::Monthly, 'icon' => '📹'],
            
            // Servicios básicos
            ['name' => 'Plan Postpago Claro', 'amount' => 69.90, 'frequency' => PaymentFrequency::Monthly, 'icon' => '📱'],
            ['name' => 'Internet Fibra Movistar', 'amount' => 89.90, 'frequency' => PaymentFrequency::Monthly, 'icon' => '🌐'],
            ['name' => 'Cable TV Claro', 'amount' => 79.90, 'frequency' => PaymentFrequency::Monthly, 'icon' => '📺'],
            
            // Salud y bienestar
            ['name' => 'Gimnasio Smart Fit', 'amount' => 59.90, 'frequency' => PaymentFrequency::Monthly, 'icon' => '🏋️'],
            ['name' => 'Seguro de Salud EPS', 'amount' => 180.00, 'frequency' => PaymentFrequency::Monthly, 'icon' => '🏥'],
            
            // Servicios profesionales
            ['name' => 'Office 365 Personal', 'amount' => 25.00, 'frequency' => PaymentFrequency::Monthly, 'icon' => '💼'],
            ['name' => 'Adobe Creative Cloud', 'amount' => 99.00, 'frequency' => PaymentFrequency::Monthly, 'icon' => '🎨'],
            
            // Servicios anuales
            ['name' => 'Seguro Vehicular Rimac', 'amount' => 1200.00, 'frequency' => PaymentFrequency::Yearly, 'icon' => '🚗'],
            ['name' => 'Mantenimiento Vehicular', 'amount' => 350.00, 'frequency' => PaymentFrequency::Quarterly, 'icon' => '🔧'],
        ];

        $selectedServices = collect($recurringServices)->random($count);

        foreach ($selectedServices as $service) {
            $startDate = Carbon::now()->subMonths(rand(1, 6));
            
            $scheduledPayment = ScheduledPayment::create([
                'name' => $service['name'],
                'description' => "Suscripción mensual renovable automáticamente",
                'payment_type' => PaymentType::Recurring,
                'status' => PaymentStatus::Active,
                'amount' => $service['amount'],
                'color' => $this->getRandomColor(),
                'icon' => $service['icon'],
                'start_date' => $startDate,
                'next_payment_date' => $this->calculateNextPaymentDate($startDate, $service['frequency']),
                'end_date' => null, // Recurrentes no tienen fin
                'user_id' => $user->id,
                'account_id' => $accounts->random()->id,
                'category_id' => $categories->random()->id,
                'order' => rand(1, 100),
            ]);

            // Crear configuración de horario
            PaymentSchedule::create([
                'scheduled_payment_id' => $scheduledPayment->id,
                'frequency' => $service['frequency'],
                'interval' => 1,
                'day_of_month' => $service['frequency'] === PaymentFrequency::Monthly ? rand(1, 28) : null,
                'auto_process' => true,
                'create_transaction' => true,
                'days_before_notification' => rand(1, 3),
            ]);

            // Crear historial de pagos pasados
            $this->createPaymentHistory($scheduledPayment, $startDate, rand(2, 8));
        }
    }

    private function createDebtPayments(User $user, $accounts, $categories, int $count): void
    {
        $debtTypes = [
            // Préstamos bancarios
            [
                'name' => 'Préstamo Personal BCP',
                'original_amount' => rand(5000, 25000),
                'installments' => 24,
                'creditor' => 'Banco de Crédito del Perú',
                'interest_rate' => 18.5,
                'type' => 'bank_loan'
            ],
            [
                'name' => 'Tarjeta de Crédito Interbank',
                'original_amount' => rand(2000, 8000),
                'installments' => 12,
                'creditor' => 'Interbank',
                'interest_rate' => 42.0,
                'type' => 'credit_card'
            ],
            
            // Financiamientos
            [
                'name' => 'Cuotas MacBook Pro',
                'original_amount' => rand(8000, 15000),
                'installments' => 18,
                'creditor' => 'iStore Perú',
                'interest_rate' => 15.0,
                'type' => 'electronics'
            ],
            [
                'name' => 'Financiamiento Toyota Yaris',
                'original_amount' => rand(45000, 85000),
                'installments' => 60,
                'creditor' => 'Toyota Financial Services',
                'interest_rate' => 12.5,
                'type' => 'car_loan'
            ],
            
            // Deudas personales
            [
                'name' => 'Préstamo Familiar',
                'original_amount' => rand(2000, 10000),
                'installments' => rand(6, 18),
                'creditor' => 'Carlos Mendoza (Hermano)',
                'interest_rate' => 0,
                'type' => 'personal'
            ],
            [
                'name' => 'Cuotas Refrigeradora Samsung',
                'original_amount' => rand(2500, 4500),
                'installments' => 12,
                'creditor' => 'Saga Falabella',
                'interest_rate' => 25.0,
                'type' => 'appliance'
            ],
        ];

        $selectedDebts = collect($debtTypes)->random($count);

        foreach ($selectedDebts as $debt) {
            $startDate = Carbon::now()->subMonths(rand(3, 18));
            $installmentAmount = $debt['original_amount'] / $debt['installments'];
            $paidInstallments = rand(1, min($debt['installments'] - 2, 10));
            $paidAmount = $paidInstallments * $installmentAmount;
            $remainingAmount = $debt['original_amount'] - $paidAmount;
            
            // Determinar estado (algunos completados, otros activos)
            $status = $paidInstallments >= $debt['installments'] 
                ? PaymentStatus::Completed 
                : (rand(1, 10) > 8 ? PaymentStatus::Paused : PaymentStatus::Active);

            $scheduledPayment = ScheduledPayment::create([
                'name' => $debt['name'],
                'description' => "Deuda en {$debt['installments']} cuotas mensuales",
                'payment_type' => PaymentType::Debt,
                'status' => $status,
                'amount' => $installmentAmount,
                'color' => $this->getRandomColor(),
                'icon' => $this->getDebtIcon($debt['type']),
                'start_date' => $startDate,
                'next_payment_date' => $status === PaymentStatus::Active 
                    ? Carbon::now()->addDays(rand(1, 30)) 
                    : null,
                'end_date' => $startDate->copy()->addMonths($debt['installments']),
                'user_id' => $user->id,
                'account_id' => $accounts->random()->id,
                'category_id' => $categories->random()->id,
                'order' => rand(1, 100),
            ]);

            // Crear detalles de deuda
            DebtDetail::create([
                'scheduled_payment_id' => $scheduledPayment->id,
                'original_amount' => $debt['original_amount'],
                'remaining_amount' => $remainingAmount,
                'paid_amount' => $paidAmount,
                'total_installments' => $debt['installments'],
                'paid_installments' => $paidInstallments,
                'installment_amount' => $installmentAmount,
                'interest_rate' => $debt['interest_rate'],
                'creditor' => $debt['creditor'],
                'reference_number' => 'DEBT-' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'due_date' => Carbon::now()->addDays(rand(5, 45)),
                'late_fee' => rand(0, 1) ? 0 : rand(25, 150),
                'days_overdue' => rand(0, 1) ? 0 : rand(0, 30),
            ]);

            // Crear horario de pago (mensual para deudas)
            PaymentSchedule::create([
                'scheduled_payment_id' => $scheduledPayment->id,
                'frequency' => PaymentFrequency::Monthly,
                'interval' => 1,
                'day_of_month' => rand(1, 28),
                'max_occurrences' => $debt['installments'],
                'occurrences_count' => $paidInstallments,
                'auto_process' => true,
                'create_transaction' => true,
                'days_before_notification' => 3,
            ]);

            // Crear historial de pagos
            $this->createPaymentHistory($scheduledPayment, $startDate, $paidInstallments);
        }
    }

    private function createOneTimePayments(User $user, $accounts, $categories, int $count): void
    {
        $oneTimePayments = [
            ['name' => 'Recibo de Luz Enel', 'amount' => rand(85, 180), 'icon' => '💡'],
            ['name' => 'Factura Agua Sedapal', 'amount' => rand(45, 95), 'icon' => '🚰'],
            ['name' => 'Impuesto Predial 2025', 'amount' => rand(850, 2500), 'icon' => '🏠'],
            ['name' => 'Matrícula Universidad', 'amount' => rand(1200, 3500), 'icon' => '🎓'],
            ['name' => 'Consulta Médica Especialista', 'amount' => rand(150, 450), 'icon' => '🏥'],
            ['name' => 'Renovación Licencia de Conducir', 'amount' => 162.30, 'icon' => '🚗'],
            ['name' => 'Cumpleaños de María', 'amount' => rand(200, 500), 'icon' => '🎂'],
            ['name' => 'Reparación Laptop', 'amount' => rand(180, 650), 'icon' => '💻'],
            ['name' => 'Evento Navideño Empresa', 'amount' => rand(300, 800), 'icon' => '🎄'],
            ['name' => 'Revisión Técnica Vehicular', 'amount' => 72.50, 'icon' => '🔍'],
        ];

        $selectedPayments = collect($oneTimePayments)->random($count);

        foreach ($selectedPayments as $payment) {
            $scheduledDate = Carbon::now()->addDays(rand(1, 90));
            $status = $scheduledDate->isPast() ? PaymentStatus::Completed : PaymentStatus::Active;

            $scheduledPayment = ScheduledPayment::create([
                'name' => $payment['name'],
                'description' => "Pago único programado",
                'payment_type' => PaymentType::OneTime,
                'status' => $status,
                'amount' => $payment['amount'],
                'color' => $this->getRandomColor(),
                'icon' => $payment['icon'],
                'start_date' => $scheduledDate->copy()->subDay(),
                'next_payment_date' => $status === PaymentStatus::Active ? $scheduledDate : null,
                'end_date' => $scheduledDate,
                'user_id' => $user->id,
                'account_id' => $accounts->random()->id,
                'category_id' => $categories->random()->id,
                'order' => rand(1, 100),
            ]);

            // Los pagos únicos no necesitan PaymentSchedule
            // pero si necesitan historial si ya fueron procesados
            if ($status === PaymentStatus::Completed) {
                $this->createPaymentHistory($scheduledPayment, $scheduledDate->copy()->subDay(), 1);
            }
        }
    }

    private function createPaymentHistory(ScheduledPayment $scheduledPayment, Carbon $startDate, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $scheduledDate = $startDate->copy()->addMonths($i);
            
            // No crear historial futuro
            if ($scheduledDate->isFuture()) {
                break;
            }

            $status = $this->getRandomHistoryStatus();
            
            PaymentHistory::create([
                'scheduled_payment_id' => $scheduledPayment->id,
                'amount' => $status === 'failed' || $status === 'skipped' ? 0 : $scheduledPayment->amount,
                'planned_amount' => $scheduledPayment->amount,
                'status' => $status,
                'scheduled_date' => $scheduledDate,
                'processed_date' => in_array($status, ['pending']) ? null : $scheduledDate->copy()->addHours(rand(1, 48)),
                'due_date' => $scheduledDate->copy()->addDays(rand(1, 15)),
                'payment_method' => in_array($status, ['paid', 'partial']) ? $this->getRandomPaymentMethod() : null,
                'reference_number' => in_array($status, ['paid', 'partial']) ? 'PAY-' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT) : null,
                'notes' => $this->getRandomNotes($status),
                'failure_reason' => $status === 'failed' ? $this->getRandomFailureReason() : null,
            ]);
        }
    }

    // Métodos auxiliares

    private function calculateNextPaymentDate(Carbon $startDate, PaymentFrequency $frequency): Carbon
    {
        $nextDate = $startDate->copy();
        
        return match ($frequency) {
            PaymentFrequency::Monthly => $nextDate->addMonth(),
            PaymentFrequency::Quarterly => $nextDate->addMonths(3),
            PaymentFrequency::Yearly => $nextDate->addYear(),
            default => $nextDate->addMonth(),
        };
    }

    private function getRandomColor(): string
    {
        $colors = [
            '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
            '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1'
        ];
        return $colors[array_rand($colors)];
    }

    private function getDebtIcon(string $type): string
    {
        return match ($type) {
            'bank_loan' => '🏦',
            'credit_card' => '💳',
            'electronics' => '💻',
            'car_loan' => '🚗',
            'personal' => '👥',
            'appliance' => '🏠',
            default => '💰',
        };
    }

    private function getRandomHistoryStatus(): string
    {
        $statuses = ['paid', 'paid', 'paid', 'paid', 'paid', 'paid', 'failed', 'skipped', 'partial']; // Más pagos exitosos
        return $statuses[array_rand($statuses)];
    }

    private function getRandomPaymentMethod(): string
    {
        $methods = ['transferencia_bancaria', 'tarjeta_debito', 'tarjeta_credito', 'billetera_digital', 'efectivo'];
        return $methods[array_rand($methods)];
    }

    private function getRandomNotes(string $status): ?string
    {
        if (rand(1, 10) > 3) return null; // 70% sin notas
        
        $notes = match ($status) {
            'paid' => ['Pago procesado exitosamente', 'Transacción completada'],
            'failed' => ['Error en el procesamiento', 'Fondos insuficientes'],
            'skipped' => ['Omitido por el usuario', 'Pospuesto al siguiente periodo'],
            'partial' => ['Pago parcial realizado', 'Pendiente completar saldo'],
            default => [null],
        };

        return $notes[array_rand($notes)];
    }

    private function getRandomFailureReason(): string
    {
        $reasons = [
            'Fondos insuficientes en la cuenta',
            'Cuenta bloqueada temporalmente',
            'Error en el sistema bancario',
            'Tarjeta expirada',
            'Límite de transacciones excedido',
        ];
        return $reasons[array_rand($reasons)];
    }
}
