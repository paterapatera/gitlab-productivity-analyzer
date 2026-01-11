<?php

use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\Repositories\EloquentCommitUserMonthlyAggregationRepository;
use Illuminate\Support\Collection;

require_once __DIR__.'/Helpers.php';
require_once __DIR__.'/../../Helpers.php';

function getEloquentCommitUserMonthlyAggregationRepository(): EloquentCommitUserMonthlyAggregationRepository
{
    return app(EloquentCommitUserMonthlyAggregationRepository::class);
}

describe('save()メソッド', function () {
    test('新規集計データを保存できる', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $aggregation = createCommitUserMonthlyAggregation(
            projectId: 1,
            branchName: 'main',
            authorEmail: 'test@example.com',
            year: 2024,
            month: 1,
            authorName: 'John Doe',
            totalAdditions: 100,
            totalDeletions: 50,
            commitCount: 5
        );

        $result = $repository->save($aggregation);

        expect($result)->toBeInstanceOf(CommitUserMonthlyAggregation::class);
        expect($result->id->projectId->value)->toBe(1);
        expect($result->id->branchName->value)->toBe('main');
        expect($result->id->authorEmail->value)->toBe('test@example.com');
        expect($result->id->year->value)->toBe(2024);
        expect($result->id->month->value)->toBe(1);
        expect($result->totalAdditions->value)->toBe(100);
        expect($result->totalDeletions->value)->toBe(50);
        expect($result->commitCount->value)->toBe(5);
        expect($result->authorName->value)->toBe('John Doe');
    });

    test('既存集計データを更新できる', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $aggregation1 = createCommitUserMonthlyAggregation(
            projectId: 1,
            branchName: 'main',
            authorEmail: 'test@example.com',
            year: 2024,
            month: 1,
            authorName: 'John Doe',
            totalAdditions: 100,
            totalDeletions: 50,
            commitCount: 5
        );
        $repository->save($aggregation1);

        $aggregation2 = createCommitUserMonthlyAggregation(
            projectId: 1,
            branchName: 'main',
            authorEmail: 'test@example.com',
            year: 2024,
            month: 1,
            authorName: 'John Doe',
            totalAdditions: 200,
            totalDeletions: 100,
            commitCount: 10
        );

        $result = $repository->save($aggregation2);

        expect($result->totalAdditions->value)->toBe(200);
        expect($result->totalDeletions->value)->toBe(100);
        expect($result->commitCount->value)->toBe(10);
    });
});

describe('saveMany()メソッド', function () {
    test('複数の集計データを一括保存できる', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $aggregations = collect([
            createCommitUserMonthlyAggregation(1, 'main', 'test1@example.com', 2024, 1, 'User 1', 100, 50, 5),
            createCommitUserMonthlyAggregation(1, 'main', 'test2@example.com', 2024, 1, 'User 2', 200, 100, 10),
            createCommitUserMonthlyAggregation(1, 'main', 'test1@example.com', 2024, 2, 'User 1', 150, 75, 8),
        ]);

        $repository->saveMany($aggregations);

        $saved = $repository->findByProjectAndBranch(
            new ProjectId(1),
            new BranchName('main')
        );

        expect($saved->count())->toBe(3);
    });
});

describe('findLatestAggregationMonth()メソッド', function () {
    test('集計データが存在する場合、最終集計月を返す', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $repository->saveMany(collect([
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 1),
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 2),
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 3),
        ]));

        $result = $repository->findLatestAggregationMonth(
            new ProjectId(1),
            new BranchName('main')
        );

        expect($result)->not->toBeNull();
        expect($result->year)->toBe(2024);
        expect($result->month)->toBe(3);
    });

    test('集計データが存在しない場合、nullを返す', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $result = $repository->findLatestAggregationMonth(
            new ProjectId(1),
            new BranchName('main')
        );

        expect($result)->toBeNull();
    });
});

describe('findByProjectAndBranch()メソッド', function () {
    test('プロジェクトとブランチで集計データを取得できる', function () {
        setupProjectForRepositoryTest(1, 'group/project1');
        setupProjectForRepositoryTest(2, 'group/project2');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $repository->saveMany(collect([
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 1),
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 2),
            createCommitUserMonthlyAggregation(1, 'develop', 'test@example.com', 2024, 1),
            createCommitUserMonthlyAggregation(2, 'main', 'test@example.com', 2024, 1),
        ]));

        $result = $repository->findByProjectAndBranch(
            new ProjectId(1),
            new BranchName('main')
        );

        expect($result->count())->toBe(2);
    });

    test('年でフィルタリングできる', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $repository->saveMany(collect([
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 1),
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 2),
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2025, 1),
        ]));

        $result = $repository->findByProjectAndBranch(
            new ProjectId(1),
            new BranchName('main'),
            year: 2024
        );

        expect($result->count())->toBe(2);
    });

    test('月の配列でフィルタリングできる', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $repository->saveMany(collect([
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 1),
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 2),
            createCommitUserMonthlyAggregation(1, 'main', 'test@example.com', 2024, 3),
        ]));

        $result = $repository->findByProjectAndBranch(
            new ProjectId(1),
            new BranchName('main'),
            year: 2024,
            months: [1, 3]
        );

        expect($result->count())->toBe(2);
    });

    test('作成者メールでフィルタリングできる', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $repository->saveMany(collect([
            createCommitUserMonthlyAggregation(1, 'main', 'test1@example.com', 2024, 1),
            createCommitUserMonthlyAggregation(1, 'main', 'test2@example.com', 2024, 1),
            createCommitUserMonthlyAggregation(1, 'main', 'test1@example.com', 2024, 2),
        ]));

        $result = $repository->findByProjectAndBranch(
            new ProjectId(1),
            new BranchName('main'),
            authorEmail: 'test1@example.com'
        );

        expect($result->count())->toBe(2);
    });

    test('集計データが存在しない場合、空のコレクションを返す', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitUserMonthlyAggregationRepository();

        $result = $repository->findByProjectAndBranch(
            new ProjectId(1),
            new BranchName('main')
        );

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->count())->toBe(0);
    });
});
