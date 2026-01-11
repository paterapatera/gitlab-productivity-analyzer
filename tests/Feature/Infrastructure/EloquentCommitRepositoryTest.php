<?php

use App\Domain\Commit;

require_once __DIR__.'/Helpers.php';
require_once __DIR__.'/../../Unit/Domain/CommitTest.php';

describe('save()メソッド', function () {
    test('新規コミットを保存できる', function () {
        $repository = getEloquentCommitRepository();

        $commit = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: 'Initial commit',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 100,
            deletions: 10
        );

        $result = $repository->save($commit);

        expect($result)->toBeInstanceOf(Commit::class);
        expect($result->id->projectId->value)->toBe(1);
        expect($result->id->branchName->value)->toBe('main');
        expect($result->id->sha->value)->toBe('a1b2c3d4e5f6789012345678901234567890abcd');
    });

    test('既存コミットを更新できる', function () {
        $repository = getEloquentCommitRepository();

        $commit1 = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: 'Old message',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 100,
            deletions: 10
        );
        $repository->save($commit1);

        $commit2 = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: 'Updated message',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 150,
            deletions: 20
        );

        $result = $repository->save($commit2);

        expect($result->message->value)->toBe('Updated message');
        expect($result->additions->value)->toBe(150);
        expect($result->deletions->value)->toBe(20);
    });

    test('nullのmessage、authorName、authorEmailを保存できる', function () {
        $repository = getEloquentCommitRepository();

        $commit = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: '',
            committedDate: '2025-01-01 12:00:00',
            authorName: null,
            authorEmail: null,
            additions: 0,
            deletions: 0
        );

        $result = $repository->save($commit);

        expect($result->message->value)->toBe('');
        expect($result->authorName->value)->toBeNull();
        expect($result->authorEmail->value)->toBeNull();
    });
});

describe('saveMany()メソッド', function () {
    test('複数のコミットを一括保存できる', function () {
        $repository = getEloquentCommitRepository();

        $commits = collect([
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
                message: 'Commit 1',
                committedDate: '2025-01-01 12:00:00',
                authorName: 'John Doe',
                authorEmail: 'john@example.com',
                additions: 100,
                deletions: 10
            ),
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'b2c3d4e5f6789012345678901234567890abcdef',
                message: 'Commit 2',
                committedDate: '2025-01-02 12:00:00',
                authorName: 'Jane Doe',
                authorEmail: 'jane@example.com',
                additions: 200,
                deletions: 20
            ),
            createCommit(
                projectId: 2,
                branchName: 'develop',
                sha: 'c3d4e5f6789012345678901234567890abcdef01',
                message: 'Commit 3',
                committedDate: '2025-01-03 12:00:00',
                authorName: 'Bob Smith',
                authorEmail: 'bob@example.com',
                additions: 300,
                deletions: 30
            ),
        ]);

        $repository->saveMany($commits);

        // データベースから確認（直接Eloquentモデルを使用）
        $count = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::count();
        expect($count)->toBe(3);
    });

    test('既存コミットと新規コミットを混在して保存できる', function () {
        $repository = getEloquentCommitRepository();

        // 既存コミットを作成
        $existing = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: 'Old message',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 100,
            deletions: 10
        );
        $repository->save($existing);

        // 既存と新規を混在
        $commits = collect([
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
                message: 'Updated message',
                committedDate: '2025-01-01 12:00:00',
                authorName: 'John Doe',
                authorEmail: 'john@example.com',
                additions: 150,
                deletions: 15
            ),
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'b2c3d4e5f6789012345678901234567890abcdef',
                message: 'New commit',
                committedDate: '2025-01-02 12:00:00',
                authorName: 'Jane Doe',
                authorEmail: 'jane@example.com',
                additions: 200,
                deletions: 20
            ),
        ]);

        $repository->saveMany($commits);

        $count = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::count();
        expect($count)->toBe(2);

        // 更新されたコミットを確認
        $updated = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 1)
            ->where('branch_name', 'main')
            ->where('sha', 'a1b2c3d4e5f6789012345678901234567890abcd')
            ->first();
        expect($updated->message)->toBe('Updated message');
        expect($updated->additions)->toBe(150);
    });
});

describe('一意性制約', function () {
    test('同じSHAでも異なるプロジェクトIDの場合は別レコードとして保存される', function () {
        $repository = getEloquentCommitRepository();

        $commit1 = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: 'Commit in project 1',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 100,
            deletions: 10
        );

        $commit2 = createCommit(
            projectId: 2,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd', // 同じSHA
            message: 'Commit in project 2',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'Jane Doe',
            authorEmail: 'jane@example.com',
            additions: 200,
            deletions: 20
        );

        $repository->save($commit1);
        $repository->save($commit2);

        $count = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::count();
        expect($count)->toBe(2);

        // それぞれが正しく保存されていることを確認
        $saved1 = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 1)
            ->where('branch_name', 'main')
            ->where('sha', 'a1b2c3d4e5f6789012345678901234567890abcd')
            ->first();
        expect($saved1)->not->toBeNull();
        expect($saved1->message)->toBe('Commit in project 1');
        expect($saved1->additions)->toBe(100);

        $saved2 = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 2)
            ->where('branch_name', 'main')
            ->where('sha', 'a1b2c3d4e5f6789012345678901234567890abcd')
            ->first();
        expect($saved2)->not->toBeNull();
        expect($saved2->message)->toBe('Commit in project 2');
        expect($saved2->additions)->toBe(200);
    });

    test('同じSHAでも異なるブランチ名の場合は別レコードとして保存される', function () {
        $repository = getEloquentCommitRepository();

        $commit1 = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: 'Commit in main branch',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 100,
            deletions: 10
        );

        $commit2 = createCommit(
            projectId: 1,
            branchName: 'develop', // 異なるブランチ
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd', // 同じSHA
            message: 'Commit in develop branch',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'Jane Doe',
            authorEmail: 'jane@example.com',
            additions: 200,
            deletions: 20
        );

        $repository->save($commit1);
        $repository->save($commit2);

        $count = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::count();
        expect($count)->toBe(2);

        // それぞれが正しく保存されていることを確認
        $saved1 = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 1)
            ->where('branch_name', 'main')
            ->where('sha', 'a1b2c3d4e5f6789012345678901234567890abcd')
            ->first();
        expect($saved1)->not->toBeNull();
        expect($saved1->message)->toBe('Commit in main branch');

        $saved2 = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 1)
            ->where('branch_name', 'develop')
            ->where('sha', 'a1b2c3d4e5f6789012345678901234567890abcd')
            ->first();
        expect($saved2)->not->toBeNull();
        expect($saved2->message)->toBe('Commit in develop branch');
    });

    test('同じ(project_id, branch_name, sha)の組み合わせで保存した場合、更新される', function () {
        $repository = getEloquentCommitRepository();

        $commit1 = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: 'Initial message',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 100,
            deletions: 10
        );

        $commit2 = createCommit(
            projectId: 1, // 同じプロジェクトID
            branchName: 'main', // 同じブランチ名
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd', // 同じSHA
            message: 'Updated message',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 150,
            deletions: 15
        );

        $repository->save($commit1);
        $repository->save($commit2);

        // 1件のみ存在することを確認（更新された）
        $count = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::count();
        expect($count)->toBe(1);

        // 更新された内容を確認
        $updated = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::where('project_id', 1)
            ->where('branch_name', 'main')
            ->where('sha', 'a1b2c3d4e5f6789012345678901234567890abcd')
            ->first();
        expect($updated->message)->toBe('Updated message');
        expect($updated->additions)->toBe(150);
        expect($updated->deletions)->toBe(15);
    });

    test('異なる(project_id, branch_name, sha)の組み合わせで複数のコミットを保存できる', function () {
        $repository = getEloquentCommitRepository();

        $commits = collect([
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
                message: 'Commit 1',
                committedDate: '2025-01-01 12:00:00',
                authorName: 'John Doe',
                authorEmail: 'john@example.com',
                additions: 100,
                deletions: 10
            ),
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'b2c3d4e5f6789012345678901234567890abcdef', // 異なるSHA
                message: 'Commit 2',
                committedDate: '2025-01-02 12:00:00',
                authorName: 'Jane Doe',
                authorEmail: 'jane@example.com',
                additions: 200,
                deletions: 20
            ),
            createCommit(
                projectId: 2, // 異なるプロジェクトID
                branchName: 'main',
                sha: 'a1b2c3d4e5f6789012345678901234567890abcd', // 同じSHAでも異なるプロジェクト
                message: 'Commit 3',
                committedDate: '2025-01-03 12:00:00',
                authorName: 'Bob Smith',
                authorEmail: 'bob@example.com',
                additions: 300,
                deletions: 30
            ),
            createCommit(
                projectId: 1,
                branchName: 'develop', // 異なるブランチ
                sha: 'a1b2c3d4e5f6789012345678901234567890abcd', // 同じSHAでも異なるブランチ
                message: 'Commit 4',
                committedDate: '2025-01-04 12:00:00',
                authorName: 'Alice Brown',
                authorEmail: 'alice@example.com',
                additions: 400,
                deletions: 40
            ),
        ]);

        $repository->saveMany($commits);

        // すべてが別レコードとして保存されることを確認
        $count = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::count();
        expect($count)->toBe(4);

        // 各コミットが正しく保存されていることを確認
        $allCommits = \App\Infrastructure\Repositories\Eloquent\CommitEloquentModel::all();
        expect($allCommits->pluck('message')->toArray())->toContain('Commit 1', 'Commit 2', 'Commit 3', 'Commit 4');
    });
});

describe('findLatestCommittedDate()メソッド', function () {
    test('最新コミット日時を取得できる', function () {
        $repository = getEloquentCommitRepository();

        // 複数のコミットを作成（異なる日時）
        $commits = collect([
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
                message: 'Commit 1',
                committedDate: '2025-01-01 12:00:00',
                authorName: 'John Doe',
                authorEmail: 'john@example.com',
                additions: 100,
                deletions: 10
            ),
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'b2c3d4e5f6789012345678901234567890abcdef',
                message: 'Commit 2',
                committedDate: '2025-01-02 12:00:00',
                authorName: 'Jane Doe',
                authorEmail: 'jane@example.com',
                additions: 200,
                deletions: 20
            ),
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'c3d4e5f6789012345678901234567890abcdef01',
                message: 'Commit 3',
                committedDate: '2025-01-03 12:00:00',
                authorName: 'Bob Smith',
                authorEmail: 'bob@example.com',
                additions: 300,
                deletions: 30
            ),
        ]);

        $repository->saveMany($commits);

        $result = $repository->findLatestCommittedDate(
            new \App\Domain\ValueObjects\ProjectId(1),
            new \App\Domain\ValueObjects\BranchName('main')
        );

        expect($result)->toBeInstanceOf(\DateTime::class);
        expect($result->format('Y-m-d H:i:s'))->toBe('2025-01-03 12:00:00');
    });

    test('コミットが存在しない場合はnullを返す', function () {
        $repository = getEloquentCommitRepository();

        $result = $repository->findLatestCommittedDate(
            new \App\Domain\ValueObjects\ProjectId(999),
            new \App\Domain\ValueObjects\BranchName('nonexistent')
        );

        expect($result)->toBeNull();
    });

    test('複数のコミットが同じ日時を持つ場合も正しく処理する', function () {
        $repository = getEloquentCommitRepository();

        // 同じ日時のコミットを複数作成
        $commits = collect([
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
                message: 'Commit 1',
                committedDate: '2025-01-01 12:00:00',
                authorName: 'John Doe',
                authorEmail: 'john@example.com',
                additions: 100,
                deletions: 10
            ),
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'b2c3d4e5f6789012345678901234567890abcdef',
                message: 'Commit 2',
                committedDate: '2025-01-01 12:00:00', // 同じ日時
                authorName: 'Jane Doe',
                authorEmail: 'jane@example.com',
                additions: 200,
                deletions: 20
            ),
            createCommit(
                projectId: 1,
                branchName: 'main',
                sha: 'c3d4e5f6789012345678901234567890abcdef01',
                message: 'Commit 3',
                committedDate: '2025-01-01 12:00:00', // 同じ日時
                authorName: 'Bob Smith',
                authorEmail: 'bob@example.com',
                additions: 300,
                deletions: 30
            ),
        ]);

        $repository->saveMany($commits);

        $result = $repository->findLatestCommittedDate(
            new \App\Domain\ValueObjects\ProjectId(1),
            new \App\Domain\ValueObjects\BranchName('main')
        );

        expect($result)->toBeInstanceOf(\DateTime::class);
        expect($result->format('Y-m-d H:i:s'))->toBe('2025-01-01 12:00:00');
    });

    test('異なるプロジェクトIDの場合は正しくフィルタリングされる', function () {
        $repository = getEloquentCommitRepository();

        // プロジェクト1のコミット
        $commit1 = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: 'Commit in project 1',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 100,
            deletions: 10
        );

        // プロジェクト2のコミット（より新しい日時）
        $commit2 = createCommit(
            projectId: 2,
            branchName: 'main',
            sha: 'b2c3d4e5f6789012345678901234567890abcdef',
            message: 'Commit in project 2',
            committedDate: '2025-01-02 12:00:00',
            authorName: 'Jane Doe',
            authorEmail: 'jane@example.com',
            additions: 200,
            deletions: 20
        );

        $repository->save($commit1);
        $repository->save($commit2);

        // プロジェクト1の最新日時を取得
        $result = $repository->findLatestCommittedDate(
            new \App\Domain\ValueObjects\ProjectId(1),
            new \App\Domain\ValueObjects\BranchName('main')
        );

        expect($result)->toBeInstanceOf(\DateTime::class);
        expect($result->format('Y-m-d H:i:s'))->toBe('2025-01-01 12:00:00');
    });

    test('異なるブランチ名の場合は正しくフィルタリングされる', function () {
        $repository = getEloquentCommitRepository();

        // mainブランチのコミット
        $commit1 = createCommit(
            projectId: 1,
            branchName: 'main',
            sha: 'a1b2c3d4e5f6789012345678901234567890abcd',
            message: 'Commit in main',
            committedDate: '2025-01-01 12:00:00',
            authorName: 'John Doe',
            authorEmail: 'john@example.com',
            additions: 100,
            deletions: 10
        );

        // developブランチのコミット（より新しい日時）
        $commit2 = createCommit(
            projectId: 1,
            branchName: 'develop',
            sha: 'b2c3d4e5f6789012345678901234567890abcdef',
            message: 'Commit in develop',
            committedDate: '2025-01-02 12:00:00',
            authorName: 'Jane Doe',
            authorEmail: 'jane@example.com',
            additions: 200,
            deletions: 20
        );

        $repository->save($commit1);
        $repository->save($commit2);

        // mainブランチの最新日時を取得
        $result = $repository->findLatestCommittedDate(
            new \App\Domain\ValueObjects\ProjectId(1),
            new \App\Domain\ValueObjects\BranchName('main')
        );

        expect($result)->toBeInstanceOf(\DateTime::class);
        expect($result->format('Y-m-d H:i:s'))->toBe('2025-01-01 12:00:00');
    });
});
