<?php

use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;

require_once __DIR__.'/Helpers.php';
require_once __DIR__.'/../../Unit/Domain/CommitTest.php';

describe('findByProjectAndBranchAndDateRange()メソッド', function () {
    test('指定された日付範囲内のコミットを取得できる', function () {
        $repository = getEloquentCommitRepository();

        // テストデータを作成
        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00'),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-02-15 12:00:00'),
            createCommit(1, 'main', 'c3d4e5f6789012345678901234567890abcde12f', 'Commit 3', '2024-03-15 12:00:00'),
            createCommit(1, 'main', 'd4e5f6789012345678901234567890abcde12f34', 'Commit 4', '2024-04-15 12:00:00'),
        ]);
        $repository->saveMany($commits);

        $result = $repository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-02-01 00:00:00'),
            new \DateTime('2024-03-31 23:59:59')
        );

        expect($result->count())->toBe(2);
        expect($result->pluck('id.sha.value')->toArray())->toContain('b2c3d4e5f6789012345678901234567890abcde1', 'c3d4e5f6789012345678901234567890abcde12f');
    });

    test('開始日と終了日が同じ場合、その日のコミットを取得できる', function () {
        $repository = getEloquentCommitRepository();

        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00'),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-01-15 13:00:00'),
            createCommit(1, 'main', 'c3d4e5f6789012345678901234567890abcde12f', 'Commit 3', '2024-01-16 12:00:00'),
        ]);
        $repository->saveMany($commits);

        $result = $repository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-01-15 00:00:00'),
            new \DateTime('2024-01-15 23:59:59')
        );

        expect($result->count())->toBe(2);
        expect($result->pluck('id.sha.value')->toArray())->toContain('a1b2c3d4e5f6789012345678901234567890abcd', 'b2c3d4e5f6789012345678901234567890abcde1');
    });

    test('範囲外のコミットは取得されない', function () {
        $repository = getEloquentCommitRepository();

        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00'),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-02-15 12:00:00'),
            createCommit(1, 'main', 'c3d4e5f6789012345678901234567890abcde12f', 'Commit 3', '2024-03-15 12:00:00'),
        ]);
        $repository->saveMany($commits);

        $result = $repository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-02-01 00:00:00'),
            new \DateTime('2024-02-28 23:59:59')
        );

        expect($result->count())->toBe(1);
        expect($result->first()->id->sha->value)->toBe('b2c3d4e5f6789012345678901234567890abcde1');
    });

    test('異なるプロジェクトのコミットは取得されない', function () {
        setupProjectForRepositoryTest(1, 'group/project1');
        setupProjectForRepositoryTest(2, 'group/project2');

        $repository = getEloquentCommitRepository();

        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00'),
            createCommit(2, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-01-15 12:00:00'),
        ]);
        $repository->saveMany($commits);

        $result = $repository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-01-01 00:00:00'),
            new \DateTime('2024-01-31 23:59:59')
        );

        expect($result->count())->toBe(1);
        expect($result->first()->id->projectId->value)->toBe(1);
    });

    test('異なるブランチのコミットは取得されない', function () {
        $repository = getEloquentCommitRepository();

        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00'),
            createCommit(1, 'develop', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-01-15 12:00:00'),
        ]);
        $repository->saveMany($commits);

        $result = $repository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-01-01 00:00:00'),
            new \DateTime('2024-01-31 23:59:59')
        );

        expect($result->count())->toBe(1);
        expect($result->first()->id->branchName->value)->toBe('main');
    });

    test('コミットが存在しない場合、空のコレクションを返す', function () {
        $repository = getEloquentCommitRepository();

        $result = $repository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-01-01 00:00:00'),
            new \DateTime('2024-01-31 23:59:59')
        );

        expect($result->count())->toBe(0);
    });

    test('開始日が終了日より後の場合、InvalidArgumentExceptionがスローされる', function () {
        $repository = getEloquentCommitRepository();

        expect(fn () => $repository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-02-01 00:00:00'),
            new \DateTime('2024-01-31 23:59:59')
        ))->toThrow(InvalidArgumentException::class);
    });

    test('境界値のコミット（開始日時）を含む', function () {
        $repository = getEloquentCommitRepository();

        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 00:00:00'),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-01-15 12:00:00'),
        ]);
        $repository->saveMany($commits);

        $result = $repository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-01-15 00:00:00'),
            new \DateTime('2024-01-15 23:59:59')
        );

        expect($result->count())->toBe(2);
    });

    test('境界値のコミット（終了日時）を含む', function () {
        $repository = getEloquentCommitRepository();

        $commits = collect([
            createCommit(1, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2024-01-15 12:00:00'),
            createCommit(1, 'main', 'b2c3d4e5f6789012345678901234567890abcde1', 'Commit 2', '2024-01-15 23:59:59'),
        ]);
        $repository->saveMany($commits);

        $result = $repository->findByProjectAndBranchAndDateRange(
            new ProjectId(1),
            new BranchName('main'),
            new \DateTime('2024-01-15 00:00:00'),
            new \DateTime('2024-01-15 23:59:59')
        );

        expect($result->count())->toBe(2);
    });
});
