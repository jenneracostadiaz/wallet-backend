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
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            
            // Relación con el pago programado
            $table->foreignId('scheduled_payment_id')->constrained()->onDelete('cascade');
            
            // Configuración de frecuencia (para pagos recurrentes)
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'yearly'])->nullable();
            $table->integer('interval')->default(1); // Cada X frecuencias (ej: cada 2 meses)
            $table->integer('day_of_month')->nullable(); // Día específico del mes (1-31)
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            
            // Límites de repetición
            $table->integer('max_occurrences')->nullable(); // Máximo número de ocurrencias
            $table->integer('occurrences_count')->default(0); // Contador de ocurrencias ejecutadas
            
            // Configuración adicional
            $table->boolean('auto_process')->default(true); // Procesar automáticamente
            $table->integer('days_before_notification')->default(1); // Días antes para notificar
            $table->boolean('create_transaction')->default(true); // Crear transacción automáticamente
            
            $table->timestamps();
            
            // Índices
            $table->index('scheduled_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
