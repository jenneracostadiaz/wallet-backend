<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('debt_details', function (Blueprint $table) {
            $table->id();
            
            // Relación con el pago programado
            $table->foreignId('scheduled_payment_id')->constrained()->onDelete('cascade');
            
            // Información de la deuda
            $table->decimal('original_amount', 10, 2); // Monto original de la deuda
            $table->decimal('remaining_amount', 10, 2); // Monto restante por pagar
            $table->decimal('paid_amount', 10, 2)->default(0); // Monto ya pagado
            
            // Configuración de cuotas
            $table->integer('total_installments')->default(1); // Total de cuotas
            $table->integer('paid_installments')->default(0); // Cuotas pagadas
            $table->decimal('installment_amount', 10, 2)->nullable(); // Monto por cuota
            
            // Información adicional
            $table->decimal('interest_rate', 5, 2)->nullable(); // Tasa de interés (%)
            $table->string('creditor')->nullable(); // Acreedor o entidad
            $table->string('reference_number')->nullable(); // Número de referencia
            $table->timestamp('due_date')->nullable(); // Fecha de vencimiento
            
            // Penalizaciones
            $table->decimal('late_fee', 10, 2)->default(0); // Mora por retraso
            $table->integer('days_overdue')->default(0); // Días de retraso
            
            $table->timestamps();
            
            // Índices
            $table->index('scheduled_payment_id');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_details');
    }
};
