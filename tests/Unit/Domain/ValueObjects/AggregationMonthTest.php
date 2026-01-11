<?php

use App\Domain\ValueObjects\AggregationMonth;

test('正常な月（1-12）でAggregationMonthを作成できる', function () {
    $month = new AggregationMonth(1);

    expect($month->value)->toBe(1);
});

test('最小値（1）でAggregationMonthを作成できる', function () {
    $month = new AggregationMonth(1);

    expect($month->value)->toBe(1);
});

test('最大値（12）でAggregationMonthを作成できる', function () {
    $month = new AggregationMonth(12);

    expect($month->value)->toBe(12);
});

test('0の値でAggregationMonthを作成すると例外がスローされる', function () {
    expect(fn () => new AggregationMonth(0))
        ->toThrow(InvalidArgumentException::class, '月は1以上12以下である必要があります');
});

test('負の整数値でAggregationMonthを作成すると例外がスローされる', function () {
    expect(fn () => new AggregationMonth(-1))
        ->toThrow(InvalidArgumentException::class, '月は1以上12以下である必要があります');
});

test('13以上の値でAggregationMonthを作成すると例外がスローされる', function () {
    expect(fn () => new AggregationMonth(13))
        ->toThrow(InvalidArgumentException::class, '月は1以上12以下である必要があります');
});

test('nullでAggregationMonthを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new AggregationMonth(null))
        ->toThrow(TypeError::class);
});

test('同じ値のAggregationMonthは等価である', function () {
    $month1 = new AggregationMonth(1);
    $month2 = new AggregationMonth(1);

    expect($month1->equals($month2))->toBeTrue();
});

test('異なる値のAggregationMonthは等価でない', function () {
    $month1 = new AggregationMonth(1);
    $month2 = new AggregationMonth(2);

    expect($month1->equals($month2))->toBeFalse();
});
