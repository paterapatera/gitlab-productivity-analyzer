<?php

use App\Domain\ValueObjects\AuthorName;

test('正常な文字列値でAuthorNameを作成できる', function () {
    $authorName = new AuthorName('John Doe');

    expect($authorName->value)->toBe('John Doe');
});

test('空文字列でAuthorNameを作成できる', function () {
    $authorName = new AuthorName('');

    expect($authorName->value)->toBe('');
});

test('nullでAuthorNameを作成できる', function () {
    $authorName = new AuthorName(null);

    expect($authorName->value)->toBeNull();
});

test('同じ値のAuthorNameは等価である', function () {
    $authorName1 = new AuthorName('John Doe');
    $authorName2 = new AuthorName('John Doe');

    expect($authorName1->equals($authorName2))->toBeTrue();
});

test('null同士のAuthorNameは等価である', function () {
    $authorName1 = new AuthorName(null);
    $authorName2 = new AuthorName(null);

    expect($authorName1->equals($authorName2))->toBeTrue();
});

test('異なる値のAuthorNameは等価でない', function () {
    $authorName1 = new AuthorName('John Doe');
    $authorName2 = new AuthorName('Jane Doe');

    expect($authorName1->equals($authorName2))->toBeFalse();
});

test('nullと文字列のAuthorNameは等価でない', function () {
    $authorName1 = new AuthorName(null);
    $authorName2 = new AuthorName('John Doe');

    expect($authorName1->equals($authorName2))->toBeFalse();
});

test('空文字列と文字列のAuthorNameは等価でない', function () {
    $authorName1 = new AuthorName('');
    $authorName2 = new AuthorName('John Doe');

    expect($authorName1->equals($authorName2))->toBeFalse();
});
