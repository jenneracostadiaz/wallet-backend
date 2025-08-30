<?php

use App\Enums\PaymentFrequency;

describe('PaymentFrequency Enum', function () {
    it('has correct values', function () {
        expect(PaymentFrequency::Daily->value)->toBe('daily');
        expect(PaymentFrequency::Weekly->value)->toBe('weekly');
        expect(PaymentFrequency::Biweekly->value)->toBe('biweekly');
        expect(PaymentFrequency::Monthly->value)->toBe('monthly');
        expect(PaymentFrequency::Quarterly->value)->toBe('quarterly');
        expect(PaymentFrequency::Yearly->value)->toBe('yearly');
    });

    it('has correct labels', function () {
        $labels = PaymentFrequency::labels();
        
        expect($labels['daily'])->toBe('Diario');
        expect($labels['weekly'])->toBe('Semanal');
        expect($labels['biweekly'])->toBe('Quincenal');
        expect($labels['monthly'])->toBe('Mensual');
        expect($labels['quarterly'])->toBe('Trimestral');
        expect($labels['yearly'])->toBe('Anual');
    });

    it('returns correct intervals in days', function () {
        expect(PaymentFrequency::Daily->days())->toBe(1);
        expect(PaymentFrequency::Weekly->days())->toBe(7);
        expect(PaymentFrequency::Biweekly->days())->toBe(14);
        expect(PaymentFrequency::Monthly->days())->toBe(30);
        expect(PaymentFrequency::Quarterly->days())->toBe(90);
        expect(PaymentFrequency::Yearly->days())->toBe(365);
    });

    it('returns correct descriptions', function () {
        expect(PaymentFrequency::Daily->description())->toBe('Todos los días');
        expect(PaymentFrequency::Weekly->description())->toBe('Una vez por semana');
        expect(PaymentFrequency::Biweekly->description())->toBe('Cada 15 días');
        expect(PaymentFrequency::Monthly->description())->toBe('Una vez al mes');
        expect(PaymentFrequency::Quarterly->description())->toBe('Cada 3 meses');
        expect(PaymentFrequency::Yearly->description())->toBe('Una vez al año');
    });

    it('can get all cases', function () {
        $cases = PaymentFrequency::cases();
        
        expect($cases)->toHaveCount(6);
        expect($cases[0])->toBe(PaymentFrequency::Daily);
        expect($cases[1])->toBe(PaymentFrequency::Weekly);
        expect($cases[2])->toBe(PaymentFrequency::Biweekly);
        expect($cases[3])->toBe(PaymentFrequency::Monthly);
        expect($cases[4])->toBe(PaymentFrequency::Quarterly);
        expect($cases[5])->toBe(PaymentFrequency::Yearly);
    });
});
