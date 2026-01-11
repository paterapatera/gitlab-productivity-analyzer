<?php

use App\Domain\CommitCollectionHistory;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitCollectionHistoryId;
use App\Domain\ValueObjects\CommittedDate;
use App\Domain\ValueObjects\ProjectId;

if (! function_exists('createCommitCollectionHistory')) {
    function createCommitCollectionHistory(
        int $projectId = 123,
        string $branchName = 'main',
        string $latestCommittedDate = '2024-01-01 12:00:00'
    ): CommitCollectionHistory {
        return new CommitCollectionHistory(
            id: new CommitCollectionHistoryId(
                projectId: new ProjectId($projectId),
                branchName: new BranchName($branchName)
            ),
            latestCommittedDate: new CommittedDate(new \DateTime($latestCommittedDate))
        );
    }
}

test('すべてのフィールドでCommitCollectionHistoryエンティティを作成できる', function () {
    $history = createCommitCollectionHistory();

    expect($history->id)->toBeInstanceOf(CommitCollectionHistoryId::class);
    expect($history->id->projectId)->toBeInstanceOf(ProjectId::class);
    expect($history->id->branchName)->toBeInstanceOf(BranchName::class);
    expect($history->latestCommittedDate)->toBeInstanceOf(CommittedDate::class);
});

test('CommitCollectionHistoryエンティティは不変である', function () {
    $history = createCommitCollectionHistory();

    expect($history)->toHaveProperty('id');
    expect($history)->toHaveProperty('latestCommittedDate');
});

describe('等価性の比較', function () {
    test('同じ値のCommitCollectionHistoryエンティティは等価である', function () {
        $history1 = createCommitCollectionHistory(123, 'main', '2024-01-01 12:00:00');
        $history2 = createCommitCollectionHistory(123, 'main', '2024-01-01 12:00:00');

        expect($history1->equals($history2))->toBeTrue();
    });

    test('異なるprojectIdのCommitCollectionHistoryエンティティは等価でない', function () {
        $history1 = createCommitCollectionHistory(123, 'main');
        $history2 = createCommitCollectionHistory(456, 'main');

        expect($history1->equals($history2))->toBeFalse();
    });

    test('異なるbranchNameのCommitCollectionHistoryエンティティは等価でない', function () {
        $history1 = createCommitCollectionHistory(123, 'main');
        $history2 = createCommitCollectionHistory(123, 'develop');

        expect($history1->equals($history2))->toBeFalse();
    });

    test('異なるlatestCommittedDateのCommitCollectionHistoryエンティティは等価でない', function () {
        $history1 = createCommitCollectionHistory(123, 'main', '2024-01-01 12:00:00');
        $history2 = createCommitCollectionHistory(123, 'main', '2024-01-02 12:00:00');

        expect($history1->equals($history2))->toBeFalse();
    });
});
