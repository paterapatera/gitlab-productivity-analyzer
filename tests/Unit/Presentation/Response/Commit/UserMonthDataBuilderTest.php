<?php

use App\Presentation\Response\Commit\UserMonthDataBuilder;

describe('UserMonthDataBuilder', function () {
    test('normalizeAuthorName() が正しく正規化する', function () {
        expect(UserMonthDataBuilder::normalizeAuthorName('John Doe'))->toBe('John Doe');
        expect(UserMonthDataBuilder::normalizeAuthorName(null))->toBe('Unknown');
        expect(UserMonthDataBuilder::normalizeAuthorName(''))->toBe('');
    });
});
