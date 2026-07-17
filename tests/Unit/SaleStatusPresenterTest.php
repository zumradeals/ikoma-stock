<?php

use App\Support\SaleStatusPresenter;

test('total=0 resolves to free regardless of payment and delivery status', function () {
    expect(SaleStatusPresenter::resolve('PAID', 'DELIVERED', 0))->toBe('free');
    expect(SaleStatusPresenter::resolve('UNPAID', 'TO_PREPARE', 0))->toBe('free');
    expect(SaleStatusPresenter::resolve('PARTIAL', 'TO_PREPARE', 0))->toBe('free');
});

test('cancelled always resolves to cancelled', function () {
    expect(SaleStatusPresenter::resolve('PAID', 'DELIVERED', 1000, isCancelled: true))->toBe('cancelled');
    expect(SaleStatusPresenter::resolve('UNPAID', 'TO_PREPARE', 0, isCancelled: true))->toBe('cancelled');
});

test('paid and delivered resolves to paid_delivered', function () {
    expect(SaleStatusPresenter::resolve('PAID', 'DELIVERED', 1000))->toBe('paid_delivered');
});

test('paid but not delivered resolves to to_deliver', function () {
    expect(SaleStatusPresenter::resolve('PAID', 'TO_PREPARE', 1000))->toBe('to_deliver');
    expect(SaleStatusPresenter::resolve('PAID', 'READY', 1000))->toBe('to_deliver');
});

test('partial payment resolves to partial', function () {
    expect(SaleStatusPresenter::resolve('PARTIAL', 'TO_PREPARE', 1000))->toBe('partial');
});

test('unpaid resolves to unpaid', function () {
    expect(SaleStatusPresenter::resolve('UNPAID', 'TO_PREPARE', 1000))->toBe('unpaid');
});
