<?php

use App\Application\Contract\GetProjects;
use Illuminate\Support\Collection;

test('GetProjectsインターフェースが存在する', function () {
    expect(interface_exists(GetProjects::class))->toBeTrue();
});

test('GetProjectsインターフェースにexecute()メソッドが定義されている', function () {
    $reflection = new ReflectionClass(GetProjects::class);

    expect($reflection->hasMethod('execute'))->toBeTrue();

    $method = $reflection->getMethod('execute');
    expect($method->isPublic())->toBeTrue();
    expect($method->getReturnType()?->getName())->toBe(Collection::class);
    expect($method->getParameters())->toHaveCount(0);
});
