<?php

use App\Domain\ValueObjects\BranchName;

test('正常な文字列値でBranchNameを作成できる', function () {
    $branchName = new BranchName('main');

    expect($branchName->value)->toBe('main');
});

test('空文字列でBranchNameを作成すると例外がスローされる', function () {
    expect(fn () => new BranchName(''))
        ->toThrow(InvalidArgumentException::class, 'ブランチ名は空文字列にできません');
});

test('空白文字のみでBranchNameを作成すると例外がスローされる', function () {
    expect(fn () => new BranchName('   '))
        ->toThrow(InvalidArgumentException::class, 'ブランチ名は空文字列にできません');
});

test('nullでBranchNameを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new BranchName(null))
        ->toThrow(TypeError::class);
});

test('同じ値のBranchNameは等価である', function () {
    $branchName1 = new BranchName('main');
    $branchName2 = new BranchName('main');

    expect($branchName1->equals($branchName2))->toBeTrue();
});

test('異なる値のBranchNameは等価でない', function () {
    $branchName1 = new BranchName('main');
    $branchName2 = new BranchName('develop');

    expect($branchName1->equals($branchName2))->toBeFalse();
});

test('255文字のBranchNameを作成できる', function () {
    $longBranchName = str_repeat('a', 255);
    $branchName = new BranchName($longBranchName);

    expect($branchName->value)->toBe($longBranchName);
});

test('256文字のBranchNameを作成すると例外がスローされる', function () {
    $tooLongBranchName = str_repeat('a', 256);

    expect(fn () => new BranchName($tooLongBranchName))
        ->toThrow(InvalidArgumentException::class, 'ブランチ名は255文字以下である必要があります');
});
