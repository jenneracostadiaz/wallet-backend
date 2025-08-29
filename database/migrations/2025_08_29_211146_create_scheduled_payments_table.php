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
        Schema::create('scheduled_payments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del pago programado
            $table->text('description')->nullable(); // Descripción opcional
            $table->enum('payment_type', ['recurring', 'debt', 'one_time']); // Tipo de pago
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled', 'overdue'])->default('active'); // Estado
            $table->decimal('amount', 10, 2); // Monto del pago
            $table->string('color', 7)->default('#3B82F6'); // Color para identificación visual
            $table->string('icon')->nullable(); // Icono opcional
            
            // Fechas importantes
            $table->timestamp('start_date'); // Fecha de inicio
            $table->timestamp('next_payment_date')->nullable(); // Próximo pago
            $table->timestamp('end_date')->nullable(); // Fecha final (opcional)
            
            // Relaciones
            $table->foreignId('account_id')->constrained()->onDelete('cascade'); // Cuenta de donde se debita
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null'); // Categoría
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario propietario
            
            // Metadatos
            $table->json('metadata')->nullable(); // Datos adicionales flexibles
            $table->integer('order')->default(0); // Orden de visualización
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'status']);
            $table->index(['payment_type', 'status']);
            $table->index('next_payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_payments');
    }
};
