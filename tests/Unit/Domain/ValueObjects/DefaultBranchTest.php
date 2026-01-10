<?php

use App\Domain\ValueObjects\DefaultBranch;

test('正常な文字列値でDefaultBranchを作成できる', function () {
    $branch = new DefaultBranch('main');

    expect($branch->value)->toBe('main');
});

test('nullでDefaultBranchを作成できる', function () {
    $branch = new DefaultBranch(null);

    expect($branch->value)->toBeNull();
});

test('同じ値のDefaultBranchは等価である', function () {
    $branch1 = new DefaultBranch('main');
    $branch2 = new DefaultBranch('main');

    expect($branch1->equals($branch2))->toBeTrue();
});

test('null同士のDefaultBranchは等価である', function () {
    $branch1 = new DefaultBranch(null);
    $branch2 = new DefaultBranch(null);

    expect($branch1->equals($branch2))->toBeTrue();
});

test('異なる値のDefaultBranchは等価でない', function () {
    $branch1 = new DefaultBranch('main');
    $branch2 = new DefaultBranch('develop');

    expect($branch1->equals($branch2))->toBeFalse();
});

test('nullと文字列のDefaultBranchは等価でない', function () {
    $branch1 = new DefaultBranch(null);
    $branch2 = new DefaultBranch('main');

    expect($branch1->equals($branch2))->toBeFalse();
});
