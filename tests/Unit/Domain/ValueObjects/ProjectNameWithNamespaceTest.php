<?php

use App\Domain\ValueObjects\ProjectNameWithNamespace;

test('正常な文字列値でProjectNameWithNamespaceを作成できる', function () {
    $name = new ProjectNameWithNamespace('group/project');

    expect($name->value)->toBe('group/project');
});

test('空文字列でProjectNameWithNamespaceを作成すると例外がスローされる', function () {
    expect(fn () => new ProjectNameWithNamespace(''))
        ->toThrow(InvalidArgumentException::class, '名前空間付きプロジェクト名は空文字列にできません');
});

test('空白文字のみでProjectNameWithNamespaceを作成すると例外がスローされる', function () {
    expect(fn () => new ProjectNameWithNamespace('   '))
        ->toThrow(InvalidArgumentException::class, '名前空間付きプロジェクト名は空文字列にできません');
});

test('nullでProjectNameWithNamespaceを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new ProjectNameWithNamespace(null))
        ->toThrow(TypeError::class);
});

test('同じ値のProjectNameWithNamespaceは等価である', function () {
    $name1 = new ProjectNameWithNamespace('group/project');
    $name2 = new ProjectNameWithNamespace('group/project');

    expect($name1->equals($name2))->toBeTrue();
});

test('異なる値のProjectNameWithNamespaceは等価でない', function () {
    $name1 = new ProjectNameWithNamespace('group/project1');
    $name2 = new ProjectNameWithNamespace('group/project2');

    expect($name1->equals($name2))->toBeFalse();
});
