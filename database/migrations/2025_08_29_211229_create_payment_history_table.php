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
        Schema::create('payment_history', function (Blueprint $table) {
            $table->id();
            
            // Relación con el pago programado
            $table->foreignId('scheduled_payment_id')->constrained()->onDelete('cascade');
            
            // Información del pago ejecutado
            $table->decimal('amount', 10, 2); // Monto pagado
            $table->decimal('planned_amount', 10, 2); // Monto planificado originalmente
            $table->enum('status', ['paid', 'pending', 'failed', 'skipped', 'partial']); // Estado del pago
            
            // Fechas
            $table->timestamp('scheduled_date'); // Fecha programada
            $table->timestamp('processed_date')->nullable(); // Fecha real de procesamiento
            $table->timestamp('due_date')->nullable(); // Fecha de vencimiento
            
            // Información adicional
            $table->string('payment_method')->nullable(); // Método de pago utilizado
            $table->string('reference_number')->nullable(); // Número de referencia del pago
            $table->text('notes')->nullable(); // Notas adicionales
            $table->text('failure_reason')->nullable(); // Razón de fallo si aplica
            
            // Relación con transacción generada (si aplica)
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            
            // Metadatos
            $table->json('metadata')->nullable(); // Información adicional flexible
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['scheduled_payment_id', 'status']);
            $table->index('scheduled_date');
            $table->index('processed_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_history');
    }
};
