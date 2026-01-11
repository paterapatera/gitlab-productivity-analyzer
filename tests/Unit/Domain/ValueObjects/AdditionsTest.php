<?php

use App\Domain\ValueObjects\Additions;

test('正常な非負整数値でAdditionsを作成できる', function () {
    $additions = new Additions(100);

    expect($additions->value)->toBe(100);
});

test('0の値でAdditionsを作成できる', function () {
    $additions = new Additions(0);

    expect($additions->value)->toBe(0);
});

test('負の整数値でAdditionsを作成すると例外がスローされる', function () {
    expect(fn () => new Additions(-1))
        ->toThrow(InvalidArgumentException::class, '追加行数は0以上の整数である必要があります');
});

test('nullでAdditionsを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new Additions(null))
        ->toThrow(TypeError::class);
});

test('同じ値のAdditionsは等価である', function () {
    $additions1 = new Additions(100);
    $additions2 = new Additions(100);

    expect($additions1->equals($additions2))->toBeTrue();
});

test('異なる値のAdditionsは等価でない', function () {
    $additions1 = new Additions(100);
    $additions2 = new Additions(200);

    expect($additions1->equals($additions2))->toBeFalse();
});
