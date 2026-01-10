<?php

use App\Domain\ValueObjects\ProjectId;

test('正常な整数値でProjectIdを作成できる', function () {
    $projectId = new ProjectId(123);
    
    expect($projectId->value)->toBe(123);
});

test('0の値でProjectIdを作成できる', function () {
    $projectId = new ProjectId(0);
    
    expect($projectId->value)->toBe(0);
});

test('負の整数値でProjectIdを作成できる', function () {
    $projectId = new ProjectId(-1);
    
    expect($projectId->value)->toBe(-1);
});

test('nullでProjectIdを作成するとTypeErrorがスローされる', function () {
    expect(fn() => new ProjectId(null))
        ->toThrow(TypeError::class);
});

test('同じ値のProjectIdは等価である', function () {
    $projectId1 = new ProjectId(123);
    $projectId2 = new ProjectId(123);
    
    expect($projectId1->equals($projectId2))->toBeTrue();
});

test('異なる値のProjectIdは等価でない', function () {
    $projectId1 = new ProjectId(123);
    $projectId2 = new ProjectId(456);
    
    expect($projectId1->equals($projectId2))->toBeFalse();
});

