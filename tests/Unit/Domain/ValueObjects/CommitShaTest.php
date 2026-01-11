<?php

use App\Domain\ValueObjects\CommitSha;

test('正常な40文字の16進数文字列でCommitShaを作成できる', function () {
    $sha = new CommitSha('a1b2c3d4e5f6789012345678901234567890abcd');

    expect($sha->value)->toBe('a1b2c3d4e5f6789012345678901234567890abcd');
});

test('39文字の文字列でCommitShaを作成すると例外がスローされる', function () {
    expect(fn () => new CommitSha('a1b2c3d4e5f6789012345678901234567890abc'))
        ->toThrow(InvalidArgumentException::class);
});

test('41文字の文字列でCommitShaを作成すると例外がスローされる', function () {
    expect(fn () => new CommitSha('a1b2c3d4e5f6789012345678901234567890abcde'))
        ->toThrow(InvalidArgumentException::class);
});

test('16進数以外の文字が含まれる場合に例外がスローされる', function () {
    expect(fn () => new CommitSha('a1b2c3d4e5f6789012345678901234567890abcz'))
        ->toThrow(InvalidArgumentException::class);
});

test('空文字列でCommitShaを作成すると例外がスローされる', function () {
    expect(fn () => new CommitSha(''))
        ->toThrow(InvalidArgumentException::class);
});

test('nullでCommitShaを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new CommitSha(null))
        ->toThrow(TypeError::class);
});

test('同じ値のCommitShaは等価である', function () {
    $sha1 = new CommitSha('a1b2c3d4e5f6789012345678901234567890abcd');
    $sha2 = new CommitSha('a1b2c3d4e5f6789012345678901234567890abcd');

    expect($sha1->equals($sha2))->toBeTrue();
});

test('異なる値のCommitShaは等価でない', function () {
    $sha1 = new CommitSha('a1b2c3d4e5f6789012345678901234567890abcd');
    $sha2 = new CommitSha('b2c3d4e5f6789012345678901234567890abcde1');

    expect($sha1->equals($sha2))->toBeFalse();
});
