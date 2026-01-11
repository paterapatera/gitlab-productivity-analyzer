<?php

use App\Application\DTO\CollectCommitsResult;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Support\Facades\Log;

require_once __DIR__.'/../Helpers.php';
require_once __DIR__.'/../../../Unit/Domain/CommitTest.php';

describe('CollectCommitsとAggregateCommitsの統合', function () {
    test('コミット収集完了後に集計処理が自動実行される', function () {
        setupProjectForTest(1, 'group/project1');
        setupHttpMockForCommits(1, 'main', [
            createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15T12:00:00Z', 'John Doe', 'john@example.com', 100, 50),
            createCommitData('b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-01-20T12:00:00Z', 'John Doe', 'john@example.com', 200, 100),
        ]);

        $service = getCollectCommitsService(null, getGitLabApiClient());

        // 現在日時を2024年2月1日に設定
        $now = new \DateTime('2024-02-01 00:00:00');
        \Carbon\Carbon::setTestNow($now);

        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        expect($result)->toBeInstanceOf(CollectCommitsResult::class);
        expect($result->hasErrors)->toBeFalse();
        expect($result->collectedCount)->toBe(2);

        // 集計データが作成されたことを確認
        $aggregationRepository = app(\App\Application\Port\CommitUserMonthlyAggregationRepository::class);
        $aggregations = $aggregationRepository->findByProjectAndBranch(new ProjectId(1), new BranchName('main'));

        expect($aggregations->count())->toBe(1);
        $aggregation = $aggregations->first();
        expect($aggregation->totalAdditions->value)->toBe(300); // 100 + 200
        expect($aggregation->totalDeletions->value)->toBe(150); // 50 + 100
        expect($aggregation->commitCount->value)->toBe(2);

        \Carbon\Carbon::setTestNow();
    });

    test('集計処理の失敗がコミット保存を妨げない', function () {
        setupProjectForTest(1, 'group/project1');
        setupHttpMockForCommits(1, 'main', [
            createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15T12:00:00Z', 'John Doe', 'john@example.com', 100, 50),
        ]);

        // AggregateCommitsサービスをモックしてエラーを発生させる
        $mockAggregateCommits = \Pest\Laravel\mock(\App\Application\Contract\AggregateCommits::class);
        $mockAggregateCommits->shouldReceive('execute')
            ->andThrow(new \Exception('Aggregation error'));

        // サービスを再作成（モックを使用）
        $service = getCollectCommitsService(
            null,
            getGitLabApiClient(),
            null,
            null,
            $mockAggregateCommits
        );

        Log::shouldReceive('error')
            ->once()
            ->with(\Mockery::on(function ($message) {
                return is_string($message) && str_contains($message, '集計処理でエラーが発生しました');
            }), \Mockery::type('array'));

        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        // コミット保存は成功している
        expect($result)->toBeInstanceOf(CollectCommitsResult::class);
        expect($result->hasErrors)->toBeFalse();
        expect($result->collectedCount)->toBe(1);

        // コミットが保存されていることを確認
        $commitRepository = getCommitRepository();
        $commits = $commitRepository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-01-01 00:00:00'),
            new \DateTime('2024-01-31 23:59:59')
        );
        expect($commits->count())->toBe(1);
    });

    test('集計処理のエラーがログに記録される', function () {
        setupProjectForTest(1, 'group/project1');
        setupHttpMockForCommits(1, 'main', [
            createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15T12:00:00Z', 'John Doe', 'john@example.com', 100, 50),
        ]);

        // AggregateCommitsサービスをモックしてエラーを発生させる
        $mockAggregateCommits = \Pest\Laravel\mock(\App\Application\Contract\AggregateCommits::class);
        $mockAggregateCommits->shouldReceive('execute')
            ->andThrow(new \Exception('Database connection error'));

        // サービスを再作成（モックを使用）
        $service = getCollectCommitsService(
            null,
            getGitLabApiClient(),
            null,
            null,
            $mockAggregateCommits
        );

        Log::shouldReceive('error')
            ->once()
            ->with(\Mockery::on(function ($message) {
                return is_string($message) && str_contains($message, '集計処理でエラーが発生しました');
            }), \Mockery::type('array'));

        $result = $service->execute(new ProjectId(1), new BranchName('main'));

        // エラーがログに記録されても、CollectCommitsResultには影響しない
        expect($result->hasErrors)->toBeFalse();
    });
});
