<?php

/**
 * Archivo de prueba para el Sistema de Pagos Programados
 * 
 * Este archivo demuestra cómo usar el sistema de pagos programados
 * implementado en el wallet personal.
 */

require_once 'vendor/autoload.php';

use App\Models\ScheduledPayment;
use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Enums\PaymentType;
use App\Enums\PaymentStatus;
use Carbon\Carbon;

echo "=== SISTEMA DE PAGOS PROGRAMADOS - EJEMPLOS DE USO ===\n\n";

// 1. Crear un pago recurrente mensual (Netflix)
echo "1. Creando pago recurrente mensual (Netflix):\n";
echo "---------------------------------------------\n";

$exampleUser = User::first();
$exampleAccount = $exampleUser->accounts->first();
$exampleCategory = $exampleUser->categories->where('name', 'LIKE', '%Entretenimiento%')->first();

$netflixPayment = [
    'name' => 'Netflix Subscription',
    'description' => 'Suscripción mensual a Netflix',
    'payment_type' => PaymentType::Recurring,
    'status' => PaymentStatus::Active,
    'amount' => 35.90,
    'color' => '#E50914', // Color rojo de Netflix
    'icon' => '📺',
    'start_date' => Carbon::now(),
    'next_payment_date' => Carbon::now()->addMonth(),
    'account_id' => $exampleAccount->id,
    'category_id' => $exampleCategory?->id,
    'user_id' => $exampleUser->id,
    'metadata' => [
        'provider' => 'Netflix',
        'plan' => 'Premium',
        'auto_renewal' => true
    ]
];

echo "Datos del pago: " . json_encode($netflixPayment, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// 2. Ejemplos de uso de scopes
echo "2. Ejemplos de consultas con scopes:\n";
echo "-----------------------------------\n";

echo "// Obtener próximos pagos\n";
echo "\$upcoming = ScheduledPayment::upcoming()->get();\n\n";

echo "// Obtener deudas activas\n";
echo "\$debts = ScheduledPayment::debts()->active()->get();\n\n";

echo "// Obtener pagos recurrentes\n";
echo "\$recurring = ScheduledPayment::recurring()->get();\n\n";

echo "// Obtener pagos vencidos\n";
echo "\$overdue = ScheduledPayment::overdue()->get();\n\n";

// 3. Ejemplo de relaciones
echo "3. Ejemplos de relaciones:\n";
echo "-------------------------\n";

echo "// Obtener el horario de un pago recurrente\n";
echo "\$payment = ScheduledPayment::find(1);\n";
echo "\$schedule = \$payment->paymentSchedule; // Relación hasOne\n\n";

echo "// Obtener el historial de pagos\n";
echo "\$history = \$payment->paymentHistory; // Relación hasMany\n\n";

echo "// Obtener detalles de deuda (si es una deuda)\n";
echo "\$debtDetails = \$payment->debtDetail; // Relación hasOne\n\n";

// 4. Tipos de pago disponibles
echo "4. Tipos de pago disponibles:\n";
echo "----------------------------\n";
foreach (PaymentType::cases() as $type) {
    echo "- {$type->value}: {$type->label()}\n";
}
echo "\n";

// 5. Estados de pago disponibles
echo "5. Estados de pago disponibles:\n";
echo "------------------------------\n";
foreach (PaymentStatus::cases() as $status) {
    echo "- {$status->value}: {$status->label()}\n";
}
echo "\n";

echo "=== FIN DE EJEMPLOS ===\n";
echo "\nEl sistema de pagos programados está completamente implementado y listo para usar!\n";
echo "\nCaracterísticas principales:\n";
echo "- ✅ Pagos recurrentes (suscripciones, servicios)\n";
echo "- ✅ Deudas (pagos únicos o en cuotas)\n";
echo "- ✅ Pagos únicos programados\n";
echo "- ✅ Relaciones completas con cuentas, categorías y usuarios\n";
echo "- ✅ Scopes útiles para consultas comunes\n";
echo "- ✅ Factories y seeders con datos realistas en español\n";
echo "- ✅ Enums para tipos y estados de pago\n";
echo "- ✅ Metadatos flexibles en formato JSON\n";
