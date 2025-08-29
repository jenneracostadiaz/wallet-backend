<?php

namespace App\Enums;

enum PaymentType: string
{
    case Recurring = 'recurring';    // Pagos recurrentes (suscripciones, servicios)
    case Debt = 'debt';             // Deudas (pagos únicos o en cuotas)
    case OneTime = 'one_time';      // Pagos únicos programados

    public static function labels(): array
    {
        return [
            self::Recurring->value => 'Pago Recurrente',
            self::Debt->value => 'Deuda',
            self::OneTime->value => 'Pago Único',
        ];
    }

    public function icon(): string
    {
        return match ($this) {
            self::Recurring => '🔄',
            self::Debt => '💳',
            self::OneTime => '📅',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Recurring => 'Pagos que se repiten en intervalos regulares',
            self::Debt => 'Deudas a pagar en una o varias cuotas',
            self::OneTime => 'Pagos programados para una fecha específica',
        };
    }
}
