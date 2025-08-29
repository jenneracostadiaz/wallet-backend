<?php

namespace App\Enums;

enum PaymentFrequency: string
{
    case Daily = 'daily';           // Diario
    case Weekly = 'weekly';         // Semanal
    case Biweekly = 'biweekly';     // Quincenal
    case Monthly = 'monthly';       // Mensual
    case Quarterly = 'quarterly';   // Trimestral
    case Yearly = 'yearly';         // Anual

    public static function labels(): array
    {
        return [
            self::Daily->value => 'Diario',
            self::Weekly->value => 'Semanal',
            self::Biweekly->value => 'Quincenal',
            self::Monthly->value => 'Mensual',
            self::Quarterly->value => 'Trimestral',
            self::Yearly->value => 'Anual',
        ];
    }

    public function days(): int
    {
        return match ($this) {
            self::Daily => 1,
            self::Weekly => 7,
            self::Biweekly => 14,
            self::Monthly => 30,
            self::Quarterly => 90,
            self::Yearly => 365,
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Daily => 'Todos los días',
            self::Weekly => 'Una vez por semana',
            self::Biweekly => 'Cada 15 días',
            self::Monthly => 'Una vez al mes',
            self::Quarterly => 'Cada 3 meses',
            self::Yearly => 'Una vez al año',
        };
    }
}
