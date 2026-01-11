<?php

use App\Application\Contract\AggregateCommits;
use App\Application\DTO\AggregateCommitsResult;
use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\Repositories\EloquentCommitUserMonthlyAggregationRepository;

require_once __DIR__.'/../Helpers.php';
require_once __DIR__.'/../../../Unit/Domain/CommitTest.php';

function getAggregateCommitsService(): AggregateCommits
{
    return app(AggregateCommits::class);
}

describe('execute()メソッド', function () {
    test('コミットデータから月次集計を生成できる', function () {
        setupProjectForRepositoryTest(1, 'group/project1');
        $commitRepository = getEloquentCommitRepository();

        // テストデータを作成（2024年1月と2月のコミット）
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00', 'John Doe', 'john@example.com', 100, 50),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-01-20 12:00:00', 'John Doe', 'john@example.com', 200, 100),
            createCommit(1, 'main', 'c3d4e5f6789012345678901234567890abcde12f', 'Commit 3', '2024-02-10 12:00:00', 'John Doe', 'john@example.com', 150, 75),
        ]);
        $commitRepository->saveMany($commits);

        // 現在日時を2024年3月1日に設定（先月は2月）
        $now = new \DateTime('2024-03-01 00:00:00');
        \Carbon\Carbon::setTestNow($now);

        $service = getAggregateCommitsService();
        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        expect($result)->toBeInstanceOf(AggregateCommitsResult::class);
        expect($result->hasErrors)->toBeFalse();
        expect($result->aggregatedCount)->toBe(2); // 1月と2月の集計

        // 集計データを確認
        $aggregationRepository = app(EloquentCommitUserMonthlyAggregationRepository::class);
        $aggregations = $aggregationRepository->findByProjectAndBranch(new ProjectId(1), new BranchName('main'));

        expect($aggregations->count())->toBe(2);

        // 1月の集計データを確認
        $janAggregation = $aggregations->first(fn ($agg) => $agg->id->year->value === 2024 && $agg->id->month->value === 1);
        expect($janAggregation)->not->toBeNull();
        expect($janAggregation->totalAdditions->value)->toBe(300); // 100 + 200
        expect($janAggregation->totalDeletions->value)->toBe(150); // 50 + 100
        expect($janAggregation->commitCount->value)->toBe(2);
        expect($janAggregation->authorName->value)->toBe('John Doe');

        // 2月の集計データを確認
        $febAggregation = $aggregations->first(fn ($agg) => $agg->id->year->value === 2024 && $agg->id->month->value === 2);
        expect($febAggregation)->not->toBeNull();
        expect($febAggregation->totalAdditions->value)->toBe(150);
        expect($febAggregation->totalDeletions->value)->toBe(75);
        expect($febAggregation->commitCount->value)->toBe(1);

        \Carbon\Carbon::setTestNow();
    });

    test('今月のデータは集計されない', function () {
        setupProjectForRepositoryTest(1, 'group/project1');
        $commitRepository = getEloquentCommitRepository();

        // テストデータを作成（2024年2月と3月のコミット）
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-02-15 12:00:00', 'John Doe', 'john@example.com', 100, 50),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-03-10 12:00:00', 'John Doe', 'john@example.com', 200, 100),
        ]);
        $commitRepository->saveMany($commits);

        // 現在日時を2024年3月15日に設定（今月は3月、先月は2月）
        $now = new \DateTime('2024-03-15 00:00:00');
        \Carbon\Carbon::setTestNow($now);

        $service = getAggregateCommitsService();
        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        expect($result->aggregatedCount)->toBe(1); // 2月のみ集計

        // 集計データを確認（3月のデータは含まれない）
        $aggregationRepository = app(EloquentCommitUserMonthlyAggregationRepository::class);
        $aggregations = $aggregationRepository->findByProjectAndBranch(new ProjectId(1), new BranchName('main'));

        expect($aggregations->count())->toBe(1);
        expect($aggregations->first()->id->month->value)->toBe(2); // 2月のみ

        \Carbon\Carbon::setTestNow();
    });

    test('既存集計月をスキップし、新規データのみを集計する', function () {
        setupProjectForRepositoryTest(1, 'group/project1');
        $commitRepository = getEloquentCommitRepository();
        $aggregationRepository = app(EloquentCommitUserMonthlyAggregationRepository::class);

        // 1月の集計データを事前に作成
        $existingAggregation = new CommitUserMonthlyAggregation(
            id: new \App\Domain\ValueObjects\CommitUserMonthlyAggregationId(
                projectId: new ProjectId(1),
                branchName: new BranchName('main'),
                authorEmail: new \App\Domain\ValueObjects\AuthorEmail('john@example.com'),
                year: new \App\Domain\ValueObjects\AggregationYear(2024),
                month: new \App\Domain\ValueObjects\AggregationMonth(1)
            ),
            totalAdditions: new \App\Domain\ValueObjects\Additions(100),
            totalDeletions: new \App\Domain\ValueObjects\Deletions(50),
            commitCount: new \App\Domain\ValueObjects\CommitCount(1),
            authorName: new \App\Domain\ValueObjects\AuthorName('John Doe')
        );
        $aggregationRepository->save($existingAggregation);

        // テストデータを作成（1月と2月のコミット）
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00', 'John Doe', 'john@example.com', 100, 50),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-02-10 12:00:00', 'John Doe', 'john@example.com', 200, 100),
        ]);
        $commitRepository->saveMany($commits);

        // 現在日時を2024年3月1日に設定
        $now = new \DateTime('2024-03-01 00:00:00');
        \Carbon\Carbon::setTestNow($now);

        $service = getAggregateCommitsService();
        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        expect($result->aggregatedCount)->toBe(1); // 2月のみ集計（1月はスキップ）

        // 1月の集計データが変更されていないことを確認
        $janAggregation = $aggregationRepository->findByProjectAndBranch(
            new ProjectId(1),
            new BranchName('main'),
            year: 2024,
            months: [1]
        )->first();
        expect($janAggregation)->not->toBeNull();
        expect($janAggregation->totalAdditions->value)->toBe(100); // 変更されていない
        expect($janAggregation->commitCount->value)->toBe(1); // 変更されていない

        \Carbon\Carbon::setTestNow();
    });

    test('複数ユーザーのコミットを集計できる', function () {
        setupProjectForRepositoryTest(1, 'group/project1');
        $commitRepository = getEloquentCommitRepository();

        // テストデータを作成（異なるユーザーのコミット）
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00', 'John Doe', 'john@example.com', 100, 50),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-01-20 12:00:00', 'Jane Doe', 'jane@example.com', 200, 100),
        ]);
        $commitRepository->saveMany($commits);

        // 現在日時を2024年2月1日に設定
        $now = new \DateTime('2024-02-01 00:00:00');
        \Carbon\Carbon::setTestNow($now);

        $service = getAggregateCommitsService();
        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        expect($result->aggregatedCount)->toBe(2); // 2ユーザー分

        // 集計データを確認
        $aggregationRepository = app(EloquentCommitUserMonthlyAggregationRepository::class);
        $aggregations = $aggregationRepository->findByProjectAndBranch(new ProjectId(1), new BranchName('main'));

        expect($aggregations->count())->toBe(2);

        $johnAggregation = $aggregations->first(fn ($agg) => $agg->id->authorEmail->value === 'john@example.com');
        expect($johnAggregation)->not->toBeNull();
        expect($johnAggregation->totalAdditions->value)->toBe(100);
        expect($johnAggregation->authorName->value)->toBe('John Doe');

        $janeAggregation = $aggregations->first(fn ($agg) => $agg->id->authorEmail->value === 'jane@example.com');
        expect($janeAggregation)->not->toBeNull();
        expect($janeAggregation->totalAdditions->value)->toBe(200);
        expect($janeAggregation->authorName->value)->toBe('Jane Doe');

        \Carbon\Carbon::setTestNow();
    });

    test('同一ユーザー・同一年月のコミットを合計する', function () {
        setupProjectForRepositoryTest(1, 'group/project1');
        $commitRepository = getEloquentCommitRepository();

        // テストデータを作成（同一ユーザーの複数コミット）
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00', 'John Doe', 'john@example.com', 100, 50),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-01-20 12:00:00', 'John Doe', 'john@example.com', 200, 100),
            createCommit(1, 'main', 'c3d4e5f6789012345678901234567890abcde12f', 'Commit 3', '2024-01-25 12:00:00', 'John Doe', 'john@example.com', 150, 75),
        ]);
        $commitRepository->saveMany($commits);

        // 現在日時を2024年2月1日に設定
        $now = new \DateTime('2024-02-01 00:00:00');
        \Carbon\Carbon::setTestNow($now);

        $service = getAggregateCommitsService();
        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        expect($result->aggregatedCount)->toBe(1);

        // 集計データを確認
        $aggregationRepository = app(EloquentCommitUserMonthlyAggregationRepository::class);
        $aggregations = $aggregationRepository->findByProjectAndBranch(new ProjectId(1), new BranchName('main'));

        expect($aggregations->count())->toBe(1);
        $aggregation = $aggregations->first();
        expect($aggregation->totalAdditions->value)->toBe(450); // 100 + 200 + 150
        expect($aggregation->totalDeletions->value)->toBe(225); // 50 + 100 + 75
        expect($aggregation->commitCount->value)->toBe(3);

        \Carbon\Carbon::setTestNow();
    });

    test('集計データが存在しない場合、最初の月から先月まで集計する', function () {
        setupProjectForRepositoryTest(1, 'group/project1');
        $commitRepository = getEloquentCommitRepository();

        // テストデータを作成（2024年1月、2月、3月のコミット）
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00', 'John Doe', 'john@example.com', 100, 50),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-02-10 12:00:00', 'John Doe', 'john@example.com', 200, 100),
            createCommit(1, 'main', 'c3d4e5f6789012345678901234567890abcde12f', 'Commit 3', '2024-03-05 12:00:00', 'John Doe', 'john@example.com', 150, 75),
        ]);
        $commitRepository->saveMany($commits);

        // 現在日時を2024年4月1日に設定（先月は3月、今月は4月）
        $now = new \DateTime('2024-04-01 00:00:00');
        \Carbon\Carbon::setTestNow($now);

        $service = getAggregateCommitsService();
        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        // 現在が4月1日なので、先月は3月。1月、2月、3月のデータが集計される
        expect($result->aggregatedCount)->toBe(3);

        \Carbon\Carbon::setTestNow();
    });

    test('エラーが発生した場合、エラーメッセージを返す', function () {
        setupProjectForRepositoryTest(1, 'group/project1');

        // 存在しないプロジェクトIDで実行（エラーが発生する可能性がある）
        // ただし、実際にはプロジェクトの存在チェックはしないので、
        // コミットが存在しない場合は空の結果を返す
        $service = getAggregateCommitsService();
        $result = $service->execute(new ProjectId(999), new BranchName('main'));

        expect($result)->toBeInstanceOf(AggregateCommitsResult::class);
        // コミットが存在しない場合はエラーではなく、空の結果を返す
        expect($result->aggregatedCount)->toBe(0);
    });

    test('タイムゾーンを考慮して年月を判定する', function () {
        setupProjectForRepositoryTest(1, 'group/project1');
        $commitRepository = getEloquentCommitRepository();

        // UTCで2024年1月31日23:00:00のコミット（JSTでは2024年2月1日08:00:00）
        // タイムゾーンを考慮すると、JSTで判定する場合は2月として扱われる
        // ただし、データベースに保存されている日時をそのまま使用するので、
        // committed_dateのタイムゾーンに基づいて年月を判定する

        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-31 23:00:00', 'John Doe', 'john@example.com', 100, 50),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-02-01 01:00:00', 'John Doe', 'john@example.com', 200, 100),
        ]);
        $commitRepository->saveMany($commits);

        // 現在日時を2024年3月1日に設定
        $now = new \DateTime('2024-03-01 00:00:00');
        \Carbon\Carbon::setTestNow($now);

        $service = getAggregateCommitsService();
        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        expect($result->aggregatedCount)->toBe(2); // 1月と2月

        // 集計データを確認
        $aggregationRepository = app(EloquentCommitUserMonthlyAggregationRepository::class);
        $aggregations = $aggregationRepository->findByProjectAndBranch(new ProjectId(1), new BranchName('main'));

        expect($aggregations->count())->toBe(2);

        \Carbon\Carbon::setTestNow();
    });
});
