<?php

use App\Presentation\Response\Commit\MonthValueBuilder;

describe('MonthValueBuilder', function () {
    test('calculateMonthTotal() が正しく計算する', function () {
        $monthValues = [1 => ['additions' => 10, 'deletions' => 5]];
        expect(MonthValueBuilder::calculateMonthTotal($monthValues, 1))->toBe(15);
        expect(MonthValueBuilder::calculateMonthTotal($monthValues, 2))->toBe(0);
    });
});
