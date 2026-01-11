<?php

use App\Domain\ValueObjects\AggregationYear;

test('正常な年（1-9999）でAggregationYearを作成できる', function () {
    $year = new AggregationYear(2024);

    expect($year->value)->toBe(2024);
});

test('最小値（1）でAggregationYearを作成できる', function () {
    $year = new AggregationYear(1);

    expect($year->value)->toBe(1);
});

test('最大値（9999）でAggregationYearを作成できる', function () {
    $year = new AggregationYear(9999);

    expect($year->value)->toBe(9999);
});

test('0の値でAggregationYearを作成すると例外がスローされる', function () {
    expect(fn () => new AggregationYear(0))
        ->toThrow(InvalidArgumentException::class, '年は1以上9999以下である必要があります');
});

test('負の整数値でAggregationYearを作成すると例外がスローされる', function () {
    expect(fn () => new AggregationYear(-1))
        ->toThrow(InvalidArgumentException::class, '年は1以上9999以下である必要があります');
});

test('10000以上の値でAggregationYearを作成すると例外がスローされる', function () {
    expect(fn () => new AggregationYear(10000))
        ->toThrow(InvalidArgumentException::class, '年は1以上9999以下である必要があります');
});

test('nullでAggregationYearを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new AggregationYear(null))
        ->toThrow(TypeError::class);
});

test('同じ値のAggregationYearは等価である', function () {
    $year1 = new AggregationYear(2024);
    $year2 = new AggregationYear(2024);

    expect($year1->equals($year2))->toBeTrue();
});

test('異なる値のAggregationYearは等価でない', function () {
    $year1 = new AggregationYear(2024);
    $year2 = new AggregationYear(2025);

    expect($year1->equals($year2))->toBeFalse();
});
