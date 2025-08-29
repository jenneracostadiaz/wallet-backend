<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Active = 'active';         // Activo y procesando pagos
    case Paused = 'paused';         // Pausado temporalmente
    case Completed = 'completed';   // Completado (para deudas y pagos únicos)
    case Cancelled = 'cancelled';   // Cancelado
    case Overdue = 'overdue';      // Vencido

    public static function labels(): array
    {
        return [
            self::Active->value => 'Activo',
            self::Paused->value => 'Pausado',
            self::Completed->value => 'Completado',
            self::Cancelled->value => 'Cancelado',
            self::Overdue->value => 'Vencido',
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Paused => 'yellow',
            self::Completed => 'blue',
            self::Cancelled => 'gray',
            self::Overdue => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Active => '✅',
            self::Paused => '⏸️',
            self::Completed => '🎉',
            self::Cancelled => '❌',
            self::Overdue => '⚠️',
        };
    }
}
