<?php

use App\Domain\ValueObjects\CommitMessage;

test('正常な文字列値でCommitMessageを作成できる', function () {
    $message = new CommitMessage('Initial commit');

    expect($message->value)->toBe('Initial commit');
});

test('空文字列でCommitMessageを作成できる', function () {
    $message = new CommitMessage('');

    expect($message->value)->toBe('');
});

test('nullでCommitMessageを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new CommitMessage(null))
        ->toThrow(TypeError::class);
});

test('同じ値のCommitMessageは等価である', function () {
    $message1 = new CommitMessage('Initial commit');
    $message2 = new CommitMessage('Initial commit');

    expect($message1->equals($message2))->toBeTrue();
});

test('空文字列同士のCommitMessageは等価である', function () {
    $message1 = new CommitMessage('');
    $message2 = new CommitMessage('');

    expect($message1->equals($message2))->toBeTrue();
});

test('異なる値のCommitMessageは等価でない', function () {
    $message1 = new CommitMessage('Initial commit');
    $message2 = new CommitMessage('Second commit');

    expect($message1->equals($message2))->toBeFalse();
});

test('空文字列と文字列のCommitMessageは等価でない', function () {
    $message1 = new CommitMessage('');
    $message2 = new CommitMessage('Initial commit');

    expect($message1->equals($message2))->toBeFalse();
});
