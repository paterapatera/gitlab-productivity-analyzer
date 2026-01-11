<?php

use App\Application\DTO\CollectCommitsResult;
use App\Application\Port\CommitRepository;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Support\Facades\Http;

require_once __DIR__.'/../Helpers.php';
require_once __DIR__.'/../../../Unit/Domain/CommitTest.php';

describe('CollectCommits Serviceの統合テスト', function () {
    describe('プロジェクトとブランチの存在検証', function () {
        test('プロジェクトが存在しない場合、エラーを返す', function () {
            $service = getCollectCommitsService();

            $result = $service->execute(
                new ProjectId(999),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();
            expect($result->errorMessage)->toBe('プロジェクトが存在しません');
            expect($result->collectedCount)->toBe(0);
            expect($result->savedCount)->toBe(0);
        });

        test('ブランチが存在しない場合、エラーを返す', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'nonexistent', [], false);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('nonexistent')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();
            expect($result->errorMessage)->not->toBeNull();
            expect($result->errorMessage)->not->toBeEmpty();
            expect($result->collectedCount)->toBe(0);
            expect($result->savedCount)->toBe(0);
        });
    });

    describe('開始日が指定された場合のフィルタリング', function () {
        test('開始日以降のコミットのみを収集する', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2025-01-15T12:00:00Z'),
                createCommitData('b2c3d4e5f6789012345678901234567890abcdef', 'Commit 2', '2025-01-20T12:00:00Z'),
            ]);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $sinceDate = new \DateTime('2025-01-10 12:00:00', new \DateTimeZone('UTC'));
            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main'),
                $sinceDate
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(2);
            expect($result->savedCount)->toBe(2);

            assertSinceParameterSent($sinceDate);
            assertCommitsSaved(1, 'main', 2);
        });
    });

    describe('開始日が指定されない場合の自動判定', function () {
        test('収集履歴が存在しない場合、すべてのコミットを収集する', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2025-01-01T12:00:00Z'),
                createCommitData('b2c3d4e5f6789012345678901234567890abcdef', 'Commit 2', '2025-01-05T12:00:00Z'),
                createCommitData('c3d4e5f6789012345678901234567890abcdef01', 'Commit 3', '2025-01-10T12:00:00Z'),
            ]);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main'),
                null
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(3);
            expect($result->savedCount)->toBe(3);

            assertSinceParameterNotSent();
            assertCommitsSaved(1, 'main', 3);
        });

        test('収集履歴が存在する場合、その日時以降のコミットのみを収集する', function () {
            setupProjectForTest(1, 'group/project1');

            // 既存のコミットを作成
            setupExistingCommits(1, 'main', [
                [
                    'sha' => 'a1b2c3d4e5f6789012345678901234567890abcd',
                    'message' => 'Old commit 1',
                    'committedDate' => '2025-01-01 12:00:00',
                    'authorName' => 'John Doe',
                    'authorEmail' => 'john@example.com',
                    'additions' => 100,
                    'deletions' => 10,
                ],
                [
                    'sha' => 'b2c3d4e5f6789012345678901234567890abcdef',
                    'message' => 'Old commit 2',
                    'committedDate' => '2025-01-05 12:00:00',
                    'authorName' => 'Jane Doe',
                    'authorEmail' => 'jane@example.com',
                    'additions' => 200,
                    'deletions' => 20,
                ],
            ]);

            // 収集履歴を作成（最新日時: 2025-01-05 12:00:00）
            setupExistingHistory(1, 'main', '2025-01-05 12:00:00');

            setupHttpMockForCommits(1, 'main', [
                createCommitData('c3d4e5f6789012345678901234567890abcdef01', 'New commit 1', '2025-01-10T12:00:00Z'),
                createCommitData('d4e5f6789012345678901234567890abcdef0123', 'New commit 2', '2025-01-15T12:00:00Z'),
            ]);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main'),
                null
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(2);
            expect($result->savedCount)->toBe(2);

            // GitLab APIにsinceパラメータが送信されたことを確認
            Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
                if (str_contains($request->url(), '/commits')) {
                    $data = $request->data();

                    return isset($data['since']) && str_contains($data['since'], '2025-01-05');
                }

                return false;
            });

            assertCommitsSaved(1, 'main', 4); // 既存2件 + 新規2件
        });

        test('収集履歴取得エラーが発生した場合、フォールバック動作として全コミットを収集する', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2025-01-01T12:00:00Z'),
                createCommitData('b2c3d4e5f6789012345678901234567890abcdef', 'Commit 2', '2025-01-05T12:00:00Z'),
            ]);

            $commitRepository = getCommitRepository();

            // エラーをスローするモックリポジトリを作成
            $mockCommitCollectionHistoryRepository = \Mockery::mock(\App\Application\Port\CommitCollectionHistoryRepository::class);
            $mockCommitCollectionHistoryRepository->shouldReceive('findById')
                ->once()
                ->andThrow(new \Exception('Database error'));
            $mockCommitCollectionHistoryRepository->shouldReceive('save')
                ->andReturnUsing(function ($history) {
                    return $history;
                });

            $service = getCollectCommitsService(null, getGitLabApiClient(), $commitRepository, $mockCommitCollectionHistoryRepository);

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main'),
                null
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(2);
            expect($result->savedCount)->toBe(2);

            assertSinceParameterNotSent();
        });
    });

    describe('コミット取得と永続化のフロー', function () {
        test('コミットを取得してデータベースに保存する', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2025-01-01T12:00:00Z', 'John Doe', 'john@example.com', 100, 10),
                createCommitData('b2c3d4e5f6789012345678901234567890abcdef', 'Second commit', '2025-01-02T12:00:00Z', 'Jane Doe', 'jane@example.com', 200, 20),
            ]);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(2);
            expect($result->savedCount)->toBe(2);

            assertCommitsSaved(1, 'main', 2);

            // コミットの内容を確認
            assertCommitContent(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', [
                'message' => 'Initial commit',
                'author_name' => 'John Doe',
                'additions' => 100,
                'deletions' => 10,
            ]);

            assertCommitContent(1, 'main', 'b2c3d4e5f6789012345678901234567890abcdef', [
                'message' => 'Second commit',
                'author_name' => 'Jane Doe',
                'additions' => 200,
                'deletions' => 20,
            ]);
        });

        test('既存のコミットを更新する', function () {
            setupProjectForTest(1, 'group/project1');

            // 既存のコミットを作成
            setupExistingCommits(1, 'main', [
                [
                    'sha' => 'a1b2c3d4e5f6789012345678901234567890abcd',
                    'message' => 'Old message',
                    'committedDate' => '2025-01-01 12:00:00',
                    'authorName' => 'John Doe',
                    'authorEmail' => 'john@example.com',
                    'additions' => 50,
                    'deletions' => 5,
                ],
            ]);

            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Updated message', '2025-01-01T12:00:00Z', 'John Doe', 'john@example.com', 100, 10),
            ]);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(1);
            expect($result->savedCount)->toBe(1);

            assertCommitsSaved(1, 'main', 1);

            // コミットが更新されたことを確認
            assertCommitContent(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', [
                'message' => 'Updated message',
                'additions' => 100,
                'deletions' => 10,
            ]);
        });
    });

    describe('エラーハンドリング', function () {
        test('GitLab API接続エラー時にエラーを返す', function () {
            setupProjectForTest(1, 'group/project1');

            Http::fake([
                'gitlab.example.com/api/v4/projects/1/repository/branches/main' => Http::response([
                    'name' => 'main',
                    'merged' => false,
                ], 200),
                'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response(
                    ['message' => 'Internal Server Error'],
                    500
                ),
            ]);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();
            expect($result->errorMessage)->not->toBeNull();
            expect($result->collectedCount)->toBe(0);
            expect($result->savedCount)->toBe(0);
        });

        test('認証エラー時にエラーを返す', function () {
            setupProjectForTest(1, 'group/project1');

            Http::fake([
                'gitlab.example.com/api/v4/projects/1/repository/branches/main' => Http::response([
                    'name' => 'main',
                    'merged' => false,
                ], 200),
                'gitlab.example.com/api/v4/projects/1/repository/commits*' => Http::response(
                    ['message' => 'Unauthorized'],
                    401
                ),
            ]);

            $service = getCollectCommitsService(null, getGitLabApiClient('https://gitlab.example.com', 'invalid-token'));

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();
            expect($result->errorMessage)->not->toBeNull();
            expect($result->collectedCount)->toBe(0);
            expect($result->savedCount)->toBe(0);
        });
    });

    describe('収集履歴の記録', function () {
        test('コミット収集完了後、収集履歴が記録される', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2025-01-01T12:00:00Z', 'John Doe', 'john@example.com', 100, 10),
                createCommitData('b2c3d4e5f6789012345678901234567890abcdef', 'Second commit', '2025-01-02T12:00:00Z', 'Jane Doe', 'jane@example.com', 200, 20),
            ]);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(2);
            expect($result->savedCount)->toBe(2);

            assertHistoryRecorded(1, 'main', '2025-01-02 12:00:00');
        });

        test('初回収集の場合、新しいレコードが作成される', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2025-01-01T12:00:00Z'),
            ]);

            // 収集履歴が存在しないことを確認
            $commitCollectionHistoryRepository = getCommitCollectionHistoryRepository();
            $historyId = new \App\Domain\ValueObjects\CommitCollectionHistoryId(
                new ProjectId(1),
                new BranchName('main')
            );
            $existingHistory = $commitCollectionHistoryRepository->findById($historyId);
            expect($existingHistory)->toBeNull();

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();

            assertHistoryRecorded(1, 'main', '2025-01-01 12:00:00');
        });

        test('既存のレコードがある場合、最新日時が更新される', function () {
            setupProjectForTest(1, 'group/project1');

            // 既存の収集履歴を作成
            setupExistingHistory(1, 'main', '2025-01-01 12:00:00');

            setupHttpMockForCommits(1, 'main', [
                createCommitData('b2c3d4e5f6789012345678901234567890abcdef', 'New commit', '2025-01-02T12:00:00Z'),
            ]);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();

            assertHistoryRecorded(1, 'main', '2025-01-02 12:00:00');
        });

        test('コミットが収集されなかった場合、収集履歴は更新されない', function () {
            setupProjectForTest(1, 'group/project1');

            // 既存の収集履歴を作成
            setupExistingHistory(1, 'main', '2025-01-01 12:00:00');

            setupHttpMockForCommits(1, 'main', []);

            $service = getCollectCommitsService(null, getGitLabApiClient());

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(0);

            // 収集履歴が更新されていないことを確認（既存の日時が保持される）
            assertHistoryRecorded(1, 'main', '2025-01-01 12:00:00');
        });

        test('コミット保存と履歴更新が同一トランザクションで実行される', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2025-01-01T12:00:00Z'),
            ]);

            // コミット保存時にエラーをスローするモックリポジトリを作成
            $mockCommitRepository = \Mockery::mock(CommitRepository::class);
            $mockCommitRepository->shouldReceive('saveMany')
                ->once()
                ->andThrow(new \Exception('Database error'));

            $service = getCollectCommitsService(null, getGitLabApiClient(), $mockCommitRepository);

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();

            assertHistoryNotRecorded(1, 'main');
        });
    });

    describe('エラーハンドリングの拡張', function () {
        test('収集履歴の保存時にデータベースエラーが発生した場合、トランザクションがロールバックされ、エラー結果が返される', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2025-01-01T12:00:00Z'),
            ]);

            $commitRepository = getCommitRepository();

            // 収集履歴の保存時にエラーをスローするモックリポジトリを作成
            $mockCommitCollectionHistoryRepository = \Mockery::mock(\App\Application\Port\CommitCollectionHistoryRepository::class);
            $mockCommitCollectionHistoryRepository->shouldReceive('findById')
                ->andReturn(null);
            $mockCommitCollectionHistoryRepository->shouldReceive('save')
                ->once()
                ->andThrow(new \Exception('Database error: Failed to save history'));

            $service = getCollectCommitsService(null, getGitLabApiClient(), $commitRepository, $mockCommitCollectionHistoryRepository);

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();
            expect($result->errorMessage)->toContain('Database error');

            assertCommitsSaved(1, 'main', 0);
        });

        test('最新コミット日時の取得エラーが発生した場合、フォールバック動作として全コミットを収集する', function () {
            setupProjectForTest(1, 'group/project1');
            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2025-01-01T12:00:00Z'),
                createCommitData('b2c3d4e5f6789012345678901234567890abcdef', 'Commit 2', '2025-01-02T12:00:00Z'),
            ]);

            $commitRepository = getCommitRepository();

            // 最新コミット日時の取得時にエラーをスローするモックリポジトリを作成
            $mockCommitCollectionHistoryRepository = \Mockery::mock(\App\Application\Port\CommitCollectionHistoryRepository::class);
            $mockCommitCollectionHistoryRepository->shouldReceive('findById')
                ->once()
                ->andThrow(new \Exception('Database error: Failed to find history'));
            $mockCommitCollectionHistoryRepository->shouldReceive('save')
                ->andReturnUsing(function ($history) {
                    return $history;
                });

            $service = getCollectCommitsService(null, getGitLabApiClient(), $commitRepository, $mockCommitCollectionHistoryRepository);

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main'),
                null // sinceDateがnullの場合、自動判定が実行される
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeFalse();
            expect($result->collectedCount)->toBe(2);
            expect($result->savedCount)->toBe(2);

            assertSinceParameterNotSent();
            assertCommitsSaved(1, 'main', 2);
        });

        test('トランザクション内でデータベースエラーが発生した場合、既存のコミットもロールバックされる', function () {
            setupProjectForTest(1, 'group/project1');

            // 既存のコミットを作成
            setupExistingCommits(1, 'main', [
                [
                    'sha' => 'a1b2c3d4e5f6789012345678901234567890abcd',
                    'message' => 'Existing commit',
                    'committedDate' => '2025-01-01 12:00:00',
                    'authorName' => 'John Doe',
                    'authorEmail' => 'john@example.com',
                    'additions' => 100,
                    'deletions' => 10,
                ],
            ]);

            setupHttpMockForCommits(1, 'main', [
                createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'New commit', '2025-01-02T12:00:00Z'),
            ]);

            // コミット保存時にエラーをスローするモックリポジトリを作成
            $mockCommitRepository = \Mockery::mock(CommitRepository::class);
            $mockCommitRepository->shouldReceive('saveMany')
                ->once()
                ->andThrow(new \Illuminate\Database\QueryException(
                    'test',
                    'SELECT * FROM commits',
                    [],
                    new \PDOException('Database connection error')
                ));

            $service = getCollectCommitsService(null, getGitLabApiClient(), $mockCommitRepository);

            $result = $service->execute(
                new ProjectId(1),
                new BranchName('main')
            );

            expect($result)->toBeInstanceOf(CollectCommitsResult::class);
            expect($result->hasErrors)->toBeTrue();

            // 既存のコミットが保持されていることを確認（トランザクション外で保存されたため）
            assertCommitsSaved(1, 'main', 1);
            assertCommitContent(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', [
                'sha' => 'a1b2c3d4e5f6789012345678901234567890abcd',
            ]);
        });
    });
});
