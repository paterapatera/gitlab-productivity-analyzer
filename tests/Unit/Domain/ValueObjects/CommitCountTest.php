<?php

use App\Domain\ValueObjects\CommitCount;

test('正常な非負整数値でCommitCountを作成できる', function () {
    $commitCount = new CommitCount(10);

    expect($commitCount->value)->toBe(10);
});

test('0の値でCommitCountを作成できる', function () {
    $commitCount = new CommitCount(0);

    expect($commitCount->value)->toBe(0);
});

test('負の整数値でCommitCountを作成すると例外がスローされる', function () {
    expect(fn () => new CommitCount(-1))
        ->toThrow(InvalidArgumentException::class, 'コミット数は0以上の整数である必要があります');
});

test('nullでCommitCountを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new CommitCount(null))
        ->toThrow(TypeError::class);
});

test('同じ値のCommitCountは等価である', function () {
    $commitCount1 = new CommitCount(10);
    $commitCount2 = new CommitCount(10);

    expect($commitCount1->equals($commitCount2))->toBeTrue();
});

test('異なる値のCommitCountは等価でない', function () {
    $commitCount1 = new CommitCount(10);
    $commitCount2 = new CommitCount(20);

    expect($commitCount1->equals($commitCount2))->toBeFalse();
});
