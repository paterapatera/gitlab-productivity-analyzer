<?php

use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\CommitCount;
use App\Domain\ValueObjects\CommitUserMonthlyAggregationId;
use App\Domain\ValueObjects\Deletions;

require_once __DIR__.'/../../Helpers.php';

test('すべてのフィールドでCommitUserMonthlyAggregationエンティティを作成できる', function () {
    $aggregation = createCommitUserMonthlyAggregation();

    expect($aggregation->id)->toBeInstanceOf(CommitUserMonthlyAggregationId::class);
    expect($aggregation->totalAdditions)->toBeInstanceOf(Additions::class);
    expect($aggregation->totalDeletions)->toBeInstanceOf(Deletions::class);
    expect($aggregation->commitCount)->toBeInstanceOf(CommitCount::class);
    expect($aggregation->authorName)->toBeInstanceOf(AuthorName::class);
});

test('CommitUserMonthlyAggregationエンティティは不変である', function () {
    $aggregation = createCommitUserMonthlyAggregation();

    expect($aggregation)->toHaveProperty('id');
    expect($aggregation)->toHaveProperty('totalAdditions');
    expect($aggregation)->toHaveProperty('totalDeletions');
    expect($aggregation)->toHaveProperty('commitCount');
    expect($aggregation)->toHaveProperty('authorName');
});

describe('等価性の比較', function () {
    test('同じ値のCommitUserMonthlyAggregationエンティティは等価である', function () {
        $aggregation1 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 100, 50, 5);
        $aggregation2 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 100, 50, 5);

        expect($aggregation1->equals($aggregation2))->toBeTrue();
    });

    test('idが異なるCommitUserMonthlyAggregationエンティティは等価でない', function () {
        $aggregation1 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1);
        $aggregation2 = createCommitUserMonthlyAggregation(456, 'main', 'test@example.com', 2024, 1);

        expect($aggregation1->equals($aggregation2))->toBeFalse();
    });

    test('totalAdditionsが異なるCommitUserMonthlyAggregationエンティティは等価でない', function () {
        $aggregation1 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 100, 50, 5);
        $aggregation2 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 200, 50, 5);

        expect($aggregation1->equals($aggregation2))->toBeFalse();
    });

    test('totalDeletionsが異なるCommitUserMonthlyAggregationエンティティは等価でない', function () {
        $aggregation1 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 100, 50, 5);
        $aggregation2 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 100, 100, 5);

        expect($aggregation1->equals($aggregation2))->toBeFalse();
    });

    test('commitCountが異なるCommitUserMonthlyAggregationエンティティは等価でない', function () {
        $aggregation1 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 100, 50, 5);
        $aggregation2 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 100, 50, 10);

        expect($aggregation1->equals($aggregation2))->toBeFalse();
    });

    test('authorNameが異なるCommitUserMonthlyAggregationエンティティは等価でない', function () {
        $aggregation1 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 100, 50, 5);
        $aggregation2 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'Jane Doe', 100, 50, 5);

        expect($aggregation1->equals($aggregation2))->toBeFalse();
    });

    test('nullのauthorNameと値がある場合のCommitUserMonthlyAggregationエンティティは等価でない', function () {
        $aggregation1 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, null, 100, 50, 5);
        $aggregation2 = createCommitUserMonthlyAggregation(123, 'main', 'test@example.com', 2024, 1, 'John Doe', 100, 50, 5);

        expect($aggregation1->equals($aggregation2))->toBeFalse();
    });
});
