<?php

use App\Enums\PaymentStatus;

describe('PaymentStatus Enum', function () {
    it('has correct values', function () {
        expect(PaymentStatus::Active->value)->toBe('active');
        expect(PaymentStatus::Paused->value)->toBe('paused');
        expect(PaymentStatus::Completed->value)->toBe('completed');
        expect(PaymentStatus::Cancelled->value)->toBe('cancelled');
        expect(PaymentStatus::Overdue->value)->toBe('overdue');
    });

    it('has correct labels', function () {
        $labels = PaymentStatus::labels();
        
        expect($labels['active'])->toBe('Activo');
        expect($labels['paused'])->toBe('Pausado');
        expect($labels['completed'])->toBe('Completado');
        expect($labels['cancelled'])->toBe('Cancelado');
        expect($labels['overdue'])->toBe('Vencido');
    });

    it('returns correct colors', function () {
        expect(PaymentStatus::Active->color())->toBe('green');
        expect(PaymentStatus::Paused->color())->toBe('yellow');
        expect(PaymentStatus::Completed->color())->toBe('blue');
        expect(PaymentStatus::Cancelled->color())->toBe('gray');
        expect(PaymentStatus::Overdue->color())->toBe('red');
    });

    it('returns correct icons', function () {
        expect(PaymentStatus::Active->icon())->toBe('✅');
        expect(PaymentStatus::Paused->icon())->toBe('⏸️');
        expect(PaymentStatus::Completed->icon())->toBe('🎉');
        expect(PaymentStatus::Cancelled->icon())->toBe('❌');
        expect(PaymentStatus::Overdue->icon())->toBe('⚠️');
    });

    it('can get all cases', function () {
        $cases = PaymentStatus::cases();
        
        expect($cases)->toHaveCount(5);
        expect($cases[0])->toBe(PaymentStatus::Active);
        expect($cases[1])->toBe(PaymentStatus::Paused);
        expect($cases[2])->toBe(PaymentStatus::Completed);
        expect($cases[3])->toBe(PaymentStatus::Cancelled);
        expect($cases[4])->toBe(PaymentStatus::Overdue);
    });
});
