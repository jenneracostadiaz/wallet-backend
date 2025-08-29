<?php

namespace App\Enums;

enum PaymentHistoryStatus: string
{
    case Paid = 'paid';             // Pagado exitosamente
    case Pending = 'pending';       // Pendiente de pago
    case Failed = 'failed';         // Falló el pago
    case Skipped = 'skipped';       // Omitido manualmente
    case Partial = 'partial';       // Pago parcial

    public static function labels(): array
    {
        return [
            self::Paid->value => 'Pagado',
            self::Pending->value => 'Pendiente',
            self::Failed->value => 'Falló',
            self::Skipped->value => 'Omitido',
            self::Partial->value => 'Parcial',
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::Paid => 'green',
            self::Pending => 'yellow',
            self::Failed => 'red',
            self::Skipped => 'gray',
            self::Partial => 'orange',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Paid => '✅',
            self::Pending => '⏳',
            self::Failed => '❌',
            self::Skipped => '⏭️',
            self::Partial => '🔶',
        };
    }
}
