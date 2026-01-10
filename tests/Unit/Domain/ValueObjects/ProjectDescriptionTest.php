<?php

use App\Domain\ValueObjects\ProjectDescription;

test('正常な文字列値でProjectDescriptionを作成できる', function () {
    $description = new ProjectDescription('This is a project description');
    
    expect($description->value)->toBe('This is a project description');
});

test('nullでProjectDescriptionを作成できる', function () {
    $description = new ProjectDescription(null);
    
    expect($description->value)->toBeNull();
});

test('同じ値のProjectDescriptionは等価である', function () {
    $description1 = new ProjectDescription('Description');
    $description2 = new ProjectDescription('Description');
    
    expect($description1->equals($description2))->toBeTrue();
});

test('null同士のProjectDescriptionは等価である', function () {
    $description1 = new ProjectDescription(null);
    $description2 = new ProjectDescription(null);
    
    expect($description1->equals($description2))->toBeTrue();
});

test('異なる値のProjectDescriptionは等価でない', function () {
    $description1 = new ProjectDescription('Description 1');
    $description2 = new ProjectDescription('Description 2');
    
    expect($description1->equals($description2))->toBeFalse();
});

test('nullと文字列のProjectDescriptionは等価でない', function () {
    $description1 = new ProjectDescription(null);
    $description2 = new ProjectDescription('Description');
    
    expect($description1->equals($description2))->toBeFalse();
});

