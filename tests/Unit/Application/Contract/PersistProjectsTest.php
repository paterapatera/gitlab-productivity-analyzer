<?php

use App\Application\Contract\PersistProjects;
use Illuminate\Support\Collection;

test('PersistProjectsインターフェースが存在する', function () {
    expect(interface_exists(PersistProjects::class))->toBeTrue();
});

test('PersistProjectsインターフェースにexecute()メソッドが定義されている', function () {
    $reflection = new ReflectionClass(PersistProjects::class);

    expect($reflection->hasMethod('execute'))->toBeTrue();

    $method = $reflection->getMethod('execute');
    expect($method->isPublic())->toBeTrue();
    expect($method->getReturnType()?->getName())->toBe('void');
    expect($method->getParameters())->toHaveCount(1);
    expect($method->getParameters()[0]->getType()?->getName())->toBe(Collection::class);
});
