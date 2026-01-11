<?php

use App\Domain\UserInfo;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;

function createUserInfo(
    ?string $email = 'john.doe@example.com',
    ?string $name = 'John Doe'
): UserInfo {
    return new UserInfo(
        email: new AuthorEmail($email),
        name: new AuthorName($name)
    );
}

test('すべてのフィールドでUserInfoエンティティを作成できる', function () {
    $email = new AuthorEmail('john.doe@example.com');
    $name = new AuthorName('John Doe');

    $userInfo = new UserInfo(
        email: $email,
        name: $name
    );

    expect($userInfo->email)->toBe($email);
    expect($userInfo->name)->toBe($name);
    expect($userInfo->email)->toBeInstanceOf(AuthorEmail::class);
    expect($userInfo->name)->toBeInstanceOf(AuthorName::class);
});

test('UserInfoエンティティは不変である', function () {
    $userInfo = createUserInfo();

    expect($userInfo)->toHaveProperty('email');
    expect($userInfo)->toHaveProperty('name');
    expect($userInfo->email)->toBeInstanceOf(AuthorEmail::class);
    expect($userInfo->name)->toBeInstanceOf(AuthorName::class);
});

test('UserInfoエンティティはnullのValue Objectsを受け入れる', function () {
    $userInfo1 = new UserInfo(
        email: new AuthorEmail(null),
        name: new AuthorName('John Doe')
    );

    expect($userInfo1->email->value)->toBeNull();
    expect($userInfo1->name->value)->toBe('John Doe');

    $userInfo2 = new UserInfo(
        email: new AuthorEmail('john.doe@example.com'),
        name: new AuthorName(null)
    );

    expect($userInfo2->email->value)->toBe('john.doe@example.com');
    expect($userInfo2->name->value)->toBeNull();
});

describe('等価性の比較', function () {
    test('同じ値のUserInfoエンティティは等価である', function () {
        $userInfo1 = createUserInfo('john.doe@example.com', 'John Doe');
        $userInfo2 = createUserInfo('john.doe@example.com', 'John Doe');

        expect($userInfo1->equals($userInfo2))->toBeTrue();
    });

    test('異なるemailのUserInfoエンティティは等価でない', function () {
        $userInfo1 = createUserInfo('john.doe@example.com', 'John Doe');
        $userInfo2 = createUserInfo('jane.doe@example.com', 'John Doe');

        expect($userInfo1->equals($userInfo2))->toBeFalse();
    });

    test('異なるnameのUserInfoエンティティは等価でない', function () {
        $userInfo1 = createUserInfo('john.doe@example.com', 'John Doe');
        $userInfo2 = createUserInfo('john.doe@example.com', 'Jane Doe');

        expect($userInfo1->equals($userInfo2))->toBeFalse();
    });

    test('nullのemailと値がある場合のUserInfoエンティティは等価でない', function () {
        $userInfo1 = createUserInfo(null, 'John Doe');
        $userInfo2 = createUserInfo('john.doe@example.com', 'John Doe');

        expect($userInfo1->equals($userInfo2))->toBeFalse();
    });

    test('nullのnameと値がある場合のUserInfoエンティティは等価でない', function () {
        $userInfo1 = createUserInfo('john.doe@example.com', null);
        $userInfo2 = createUserInfo('john.doe@example.com', 'John Doe');

        expect($userInfo1->equals($userInfo2))->toBeFalse();
    });

    test('null同士のemailとnameのUserInfoエンティティは等価である', function () {
        $userInfo1 = createUserInfo(null, null);
        $userInfo2 = createUserInfo(null, null);

        expect($userInfo1->equals($userInfo2))->toBeTrue();
    });
});
