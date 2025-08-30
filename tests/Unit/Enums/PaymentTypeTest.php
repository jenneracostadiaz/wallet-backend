<?php

use App\Enums\PaymentType;

describe('PaymentType Enum', function () {
    it('has correct values', function () {
        expect(PaymentType::Recurring->value)->toBe('recurring');
        expect(PaymentType::Debt->value)->toBe('debt');
        expect(PaymentType::OneTime->value)->toBe('one_time');
    });

    it('has correct labels', function () {
        $labels = PaymentType::labels();
        
        expect($labels['recurring'])->toBe('Pago Recurrente');
        expect($labels['debt'])->toBe('Deuda');
        expect($labels['one_time'])->toBe('Pago Único');
    });

    it('returns correct icons', function () {
        expect(PaymentType::Recurring->icon())->toBe('🔄');
        expect(PaymentType::Debt->icon())->toBe('💳');
        expect(PaymentType::OneTime->icon())->toBe('📅');
    });

    it('returns correct descriptions', function () {
        expect(PaymentType::Recurring->description())->toBe('Pagos que se repiten en intervalos regulares');
        expect(PaymentType::Debt->description())->toBe('Deudas a pagar en una o varias cuotas');
        expect(PaymentType::OneTime->description())->toBe('Pagos programados para una fecha específica');
    });

    it('can get all cases', function () {
        $cases = PaymentType::cases();
        
        expect($cases)->toHaveCount(3);
        expect($cases[0])->toBe(PaymentType::Recurring);
        expect($cases[1])->toBe(PaymentType::Debt);
        expect($cases[2])->toBe(PaymentType::OneTime);
    });
});
