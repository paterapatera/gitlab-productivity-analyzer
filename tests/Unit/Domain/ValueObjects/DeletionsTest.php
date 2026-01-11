<?php

use App\Domain\ValueObjects\Deletions;

test('正常な非負整数値でDeletionsを作成できる', function () {
    $deletions = new Deletions(50);

    expect($deletions->value)->toBe(50);
});

test('0の値でDeletionsを作成できる', function () {
    $deletions = new Deletions(0);

    expect($deletions->value)->toBe(0);
});

test('負の整数値でDeletionsを作成すると例外がスローされる', function () {
    expect(fn () => new Deletions(-1))
        ->toThrow(InvalidArgumentException::class, '削除行数は0以上の整数である必要があります');
});

test('nullでDeletionsを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new Deletions(null))
        ->toThrow(TypeError::class);
});

test('同じ値のDeletionsは等価である', function () {
    $deletions1 = new Deletions(50);
    $deletions2 = new Deletions(50);

    expect($deletions1->equals($deletions2))->toBeTrue();
});

test('異なる値のDeletionsは等価でない', function () {
    $deletions1 = new Deletions(50);
    $deletions2 = new Deletions(100);

    expect($deletions1->equals($deletions2))->toBeFalse();
});
