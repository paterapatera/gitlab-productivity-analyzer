<?php

use App\Domain\ValueObjects\AuthorEmail;

test('正常な文字列値でAuthorEmailを作成できる', function () {
    $authorEmail = new AuthorEmail('john.doe@example.com');

    expect($authorEmail->value)->toBe('john.doe@example.com');
});

test('空文字列でAuthorEmailを作成できる', function () {
    $authorEmail = new AuthorEmail('');

    expect($authorEmail->value)->toBe('');
});

test('nullでAuthorEmailを作成できる', function () {
    $authorEmail = new AuthorEmail(null);

    expect($authorEmail->value)->toBeNull();
});

test('同じ値のAuthorEmailは等価である', function () {
    $authorEmail1 = new AuthorEmail('john.doe@example.com');
    $authorEmail2 = new AuthorEmail('john.doe@example.com');

    expect($authorEmail1->equals($authorEmail2))->toBeTrue();
});

test('null同士のAuthorEmailは等価である', function () {
    $authorEmail1 = new AuthorEmail(null);
    $authorEmail2 = new AuthorEmail(null);

    expect($authorEmail1->equals($authorEmail2))->toBeTrue();
});

test('異なる値のAuthorEmailは等価でない', function () {
    $authorEmail1 = new AuthorEmail('john.doe@example.com');
    $authorEmail2 = new AuthorEmail('jane.doe@example.com');

    expect($authorEmail1->equals($authorEmail2))->toBeFalse();
});

test('nullと文字列のAuthorEmailは等価でない', function () {
    $authorEmail1 = new AuthorEmail(null);
    $authorEmail2 = new AuthorEmail('john.doe@example.com');

    expect($authorEmail1->equals($authorEmail2))->toBeFalse();
});

test('空文字列と文字列のAuthorEmailは等価でない', function () {
    $authorEmail1 = new AuthorEmail('');
    $authorEmail2 = new AuthorEmail('john.doe@example.com');

    expect($authorEmail1->equals($authorEmail2))->toBeFalse();
});

test('255文字のAuthorEmailを作成できる', function () {
    $longEmail = str_repeat('a', 240).'@example.com'; // 255文字
    $authorEmail = new AuthorEmail($longEmail);

    expect($authorEmail->value)->toBe($longEmail);
});

test('256文字のAuthorEmailを作成すると例外がスローされる', function () {
    $tooLongEmail = str_repeat('a', 244).'@example.com'; // 244 + 12 = 256文字

    expect(fn () => new AuthorEmail($tooLongEmail))
        ->toThrow(InvalidArgumentException::class, '作成者メールは255文字以下である必要があります');
});
