<?php

use App\Domain\CommitCollectionHistory;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitCollectionHistoryId;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Support\Collection;

require_once __DIR__.'/Helpers.php';
require_once __DIR__.'/../../Helpers.php';

describe('save()メソッド', function () {
    test('新規収集履歴を保存できる', function () {
        // プロジェクトを作成（外部キー制約のため）
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitCollectionHistoryRepository();

        $history = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'main',
            latestCommittedDate: '2024-01-01 12:00:00'
        );

        $result = $repository->save($history);

        expect($result)->toBeInstanceOf(CommitCollectionHistory::class);
        expect($result->id->projectId->value)->toBe(1);
        expect($result->id->branchName->value)->toBe('main');

        // データベースから取得して確認
        $saved = $repository->findById(
            new CommitCollectionHistoryId(
                projectId: new ProjectId(1),
                branchName: new BranchName('main')
            )
        );
        expect($saved)->not->toBeNull();
        expect($saved->id->projectId->value)->toBe(1);
        expect($saved->id->branchName->value)->toBe('main');
    });

    test('既存収集履歴を更新できる', function () {
        // プロジェクトを作成（外部キー制約のため）
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitCollectionHistoryRepository();

        $history1 = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'main',
            latestCommittedDate: '2024-01-01 12:00:00'
        );
        $repository->save($history1);

        $history2 = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'main',
            latestCommittedDate: '2024-01-02 12:00:00'
        );

        $result = $repository->save($history2);

        expect($result->latestCommittedDate->value->format('Y-m-d H:i:s'))
            ->toBe('2024-01-02 12:00:00');

        // データベースから取得して確認
        $saved = $repository->findById(
            new CommitCollectionHistoryId(
                projectId: new ProjectId(1),
                branchName: new BranchName('main')
            )
        );
        expect($saved->latestCommittedDate->value->format('Y-m-d H:i:s'))
            ->toBe('2024-01-02 12:00:00');
    });

    test('異なるプロジェクトとブランチの組み合わせで複数の履歴を保存できる', function () {
        // プロジェクトを作成（外部キー制約のため）
        setupProjectForRepositoryTest(1, 'group/project1');
        setupProjectForRepositoryTest(2, 'group/project2');

        $repository = getEloquentCommitCollectionHistoryRepository();

        $history1 = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'main',
            latestCommittedDate: '2024-01-01 12:00:00'
        );
        $history2 = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'develop',
            latestCommittedDate: '2024-01-02 12:00:00'
        );
        $history3 = createCommitCollectionHistory(
            projectId: 2,
            branchName: 'main',
            latestCommittedDate: '2024-01-03 12:00:00'
        );

        $repository->save($history1);
        $repository->save($history2);
        $repository->save($history3);

        $all = $repository->findAll();
        expect($all)->toHaveCount(3);
    });
});

describe('findById()メソッド', function () {
    test('プロジェクトIDとブランチ名で収集履歴を取得できる', function () {
        // プロジェクトを作成（外部キー制約のため）
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitCollectionHistoryRepository();

        $history = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'main',
            latestCommittedDate: '2024-01-01 12:00:00'
        );
        $repository->save($history);

        $result = $repository->findById(
            new CommitCollectionHistoryId(
                projectId: new ProjectId(1),
                branchName: new BranchName('main')
            )
        );

        expect($result)->toBeInstanceOf(CommitCollectionHistory::class);
        expect($result->id->projectId->value)->toBe(1);
        expect($result->id->branchName->value)->toBe('main');
        expect($result->latestCommittedDate->value->format('Y-m-d H:i:s'))
            ->toBe('2024-01-01 12:00:00');
    });

    test('存在しないプロジェクトIDとブランチ名の場合はnullを返す', function () {
        $repository = getEloquentCommitCollectionHistoryRepository();

        $result = $repository->findById(
            new CommitCollectionHistoryId(
                projectId: new ProjectId(999),
                branchName: new BranchName('nonexistent')
            )
        );

        expect($result)->toBeNull();
    });

    test('プロジェクトIDが一致してもブランチ名が異なる場合はnullを返す', function () {
        // プロジェクトを作成（外部キー制約のため）
        setupProjectForRepositoryTest(1, 'group/project1');

        $repository = getEloquentCommitCollectionHistoryRepository();

        $history = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'main',
            latestCommittedDate: '2024-01-01 12:00:00'
        );
        $repository->save($history);

        $result = $repository->findById(
            new CommitCollectionHistoryId(
                projectId: new ProjectId(1),
                branchName: new BranchName('develop')
            )
        );

        expect($result)->toBeNull();
    });

    test('ブランチ名が一致してもプロジェクトIDが異なる場合はnullを返す', function () {
        // プロジェクトを作成（外部キー制約のため）
        setupProjectForRepositoryTest(1, 'group/project1');
        setupProjectForRepositoryTest(2, 'group/project2');

        $repository = getEloquentCommitCollectionHistoryRepository();

        $history = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'main',
            latestCommittedDate: '2024-01-01 12:00:00'
        );
        $repository->save($history);

        $result = $repository->findById(
            new CommitCollectionHistoryId(
                projectId: new ProjectId(2),
                branchName: new BranchName('main')
            )
        );

        expect($result)->toBeNull();
    });
});

describe('findAll()メソッド', function () {
    test('全収集履歴を取得できる', function () {
        // プロジェクトを作成（外部キー制約のため）
        setupProjectForRepositoryTest(1, 'group/project1');
        setupProjectForRepositoryTest(2, 'group/project2');

        $repository = getEloquentCommitCollectionHistoryRepository();

        // テストデータを作成
        $history1 = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'main',
            latestCommittedDate: '2024-01-01 12:00:00'
        );
        $history2 = createCommitCollectionHistory(
            projectId: 1,
            branchName: 'develop',
            latestCommittedDate: '2024-01-02 12:00:00'
        );
        $history3 = createCommitCollectionHistory(
            projectId: 2,
            branchName: 'main',
            latestCommittedDate: '2024-01-03 12:00:00'
        );

        $repository->save($history1);
        $repository->save($history2);
        $repository->save($history3);

        $result = $repository->findAll();

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result)->toHaveCount(3);
        expect($result->pluck('id.projectId.value')->toArray())->toContain(1, 2);
    });

    test('収集履歴が存在しない場合は空のCollectionを返す', function () {
        $repository = getEloquentCommitCollectionHistoryRepository();

        $result = $repository->findAll();

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result)->toHaveCount(0);
    });
});
