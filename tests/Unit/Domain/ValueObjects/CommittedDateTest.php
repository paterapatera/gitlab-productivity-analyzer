<?php

use App\Domain\ValueObjects\CommittedDate;

test('正常なDateTimeでCommittedDateを作成できる', function () {
    $dateTime = new \DateTime('2024-01-01 12:00:00');
    $committedDate = new CommittedDate($dateTime);

    expect($committedDate->value)->toBeInstanceOf(\DateTime::class);
    expect($committedDate->value->format('Y-m-d H:i:s'))->toBe('2024-01-01 12:00:00');
});

test('nullでCommittedDateを作成するとTypeErrorがスローされる', function () {
    expect(fn () => new CommittedDate(null))
        ->toThrow(TypeError::class);
});

test('同じ値のCommittedDateは等価である', function () {
    $dateTime1 = new \DateTime('2024-01-01 12:00:00');
    $dateTime2 = new \DateTime('2024-01-01 12:00:00');
    $committedDate1 = new CommittedDate($dateTime1);
    $committedDate2 = new CommittedDate($dateTime2);

    expect($committedDate1->equals($committedDate2))->toBeTrue();
});

test('異なる値のCommittedDateは等価でない', function () {
    $dateTime1 = new \DateTime('2024-01-01 12:00:00');
    $dateTime2 = new \DateTime('2024-01-02 12:00:00');
    $committedDate1 = new CommittedDate($dateTime1);
    $committedDate2 = new CommittedDate($dateTime2);

    expect($committedDate1->equals($committedDate2))->toBeFalse();
});

test('同じタイムスタンプで異なるDateTimeインスタンスのCommittedDateは等価である', function () {
    $dateTime1 = new \DateTime('2024-01-01 12:00:00');
    $dateTime2 = new \DateTime('2024-01-01 12:00:00');
    $committedDate1 = new CommittedDate($dateTime1);
    $committedDate2 = new CommittedDate($dateTime2);

    expect($committedDate1->equals($committedDate2))->toBeTrue();
});
