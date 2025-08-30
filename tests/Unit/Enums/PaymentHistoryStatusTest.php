<?php

use App\Enums\PaymentHistoryStatus;

describe('PaymentHistoryStatus Enum', function () {
    it('has correct values', function () {
        expect(PaymentHistoryStatus::Paid->value)->toBe('paid');
        expect(PaymentHistoryStatus::Pending->value)->toBe('pending');
        expect(PaymentHistoryStatus::Failed->value)->toBe('failed');
        expect(PaymentHistoryStatus::Skipped->value)->toBe('skipped');
        expect(PaymentHistoryStatus::Partial->value)->toBe('partial');
    });

    it('has correct labels', function () {
        $labels = PaymentHistoryStatus::labels();
        
        expect($labels['paid'])->toBe('Pagado');
        expect($labels['pending'])->toBe('Pendiente');
        expect($labels['failed'])->toBe('Falló');
        expect($labels['skipped'])->toBe('Omitido');
        expect($labels['partial'])->toBe('Parcial');
    });

    it('returns correct colors', function () {
        expect(PaymentHistoryStatus::Paid->color())->toBe('green');
        expect(PaymentHistoryStatus::Pending->color())->toBe('yellow');
        expect(PaymentHistoryStatus::Failed->color())->toBe('red');
        expect(PaymentHistoryStatus::Skipped->color())->toBe('gray');
        expect(PaymentHistoryStatus::Partial->color())->toBe('orange');
    });

    it('returns correct icons', function () {
        expect(PaymentHistoryStatus::Paid->icon())->toBe('✅');
        expect(PaymentHistoryStatus::Pending->icon())->toBe('⏳');
        expect(PaymentHistoryStatus::Failed->icon())->toBe('❌');
        expect(PaymentHistoryStatus::Skipped->icon())->toBe('⏭️');
        expect(PaymentHistoryStatus::Partial->icon())->toBe('🔶');
    });

    it('can get all cases', function () {
        $cases = PaymentHistoryStatus::cases();
        
        expect($cases)->toHaveCount(5);
        expect($cases[0])->toBe(PaymentHistoryStatus::Paid);
        expect($cases[1])->toBe(PaymentHistoryStatus::Pending);
        expect($cases[2])->toBe(PaymentHistoryStatus::Failed);
        expect($cases[3])->toBe(PaymentHistoryStatus::Skipped);
        expect($cases[4])->toBe(PaymentHistoryStatus::Partial);
    });
});
