<?php

use App\Domain\Commit;
use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitMessage;
use App\Domain\ValueObjects\CommitSha;
use App\Domain\ValueObjects\CommittedDate;
use App\Domain\ValueObjects\Deletions;
use App\Domain\ValueObjects\ProjectId;

function createCommit(
    int $projectId = 123,
    string $branchName = 'main',
    string $sha = 'a1b2c3d4e5f6789012345678901234567890abcd',
    string $message = 'Initial commit',
    string $committedDate = '2024-01-01 12:00:00',
    ?string $authorName = 'John Doe',
    ?string $authorEmail = 'john.doe@example.com',
    int $additions = 100,
    int $deletions = 50
): Commit {
    return new Commit(
        projectId: new ProjectId($projectId),
        branchName: new BranchName($branchName),
        sha: new CommitSha($sha),
        message: new CommitMessage($message),
        committedDate: new CommittedDate(new \DateTime($committedDate)),
        authorName: new AuthorName($authorName),
        authorEmail: new AuthorEmail($authorEmail),
        additions: new Additions($additions),
        deletions: new Deletions($deletions)
    );
}

test('すべてのフィールドでCommitエンティティを作成できる', function () {
    $commit = createCommit();

    expect($commit->projectId)->toBeInstanceOf(ProjectId::class);
    expect($commit->branchName)->toBeInstanceOf(BranchName::class);
    expect($commit->sha)->toBeInstanceOf(CommitSha::class);
    expect($commit->message)->toBeInstanceOf(CommitMessage::class);
    expect($commit->committedDate)->toBeInstanceOf(CommittedDate::class);
    expect($commit->authorName)->toBeInstanceOf(AuthorName::class);
    expect($commit->authorEmail)->toBeInstanceOf(AuthorEmail::class);
    expect($commit->additions)->toBeInstanceOf(Additions::class);
    expect($commit->deletions)->toBeInstanceOf(Deletions::class);
});

test('Commitエンティティは不変である', function () {
    $commit = createCommit();

    expect($commit)->toHaveProperty('projectId');
    expect($commit)->toHaveProperty('branchName');
    expect($commit)->toHaveProperty('sha');
    expect($commit)->toHaveProperty('message');
    expect($commit)->toHaveProperty('committedDate');
    expect($commit)->toHaveProperty('authorName');
    expect($commit)->toHaveProperty('authorEmail');
    expect($commit)->toHaveProperty('additions');
    expect($commit)->toHaveProperty('deletions');
});

describe('等価性の比較', function () {
    test('同じ値のCommitエンティティは等価である', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', 'john.doe@example.com', 100, 50);
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', 'john.doe@example.com', 100, 50);

        expect($commit1->equals($commit2))->toBeTrue();
    });

    test('異なるprojectIdのCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main');
        $commit2 = createCommit(456, 'main');

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('異なるbranchNameのCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main');
        $commit2 = createCommit(123, 'develop');

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('異なるshaのCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd');
        $commit2 = createCommit(123, 'main', 'b2c3d4e5f6789012345678901234567890abcde1');

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('異なるmessageのCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit');
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Second commit');

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('異なるcommittedDateのCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00');
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-02 12:00:00');

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('異なるauthorNameのCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe');
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'Jane Doe');

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('異なるauthorEmailのCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', 'john.doe@example.com');
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', 'jane.doe@example.com');

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('異なるadditionsのCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', 'john.doe@example.com', 100);
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', 'john.doe@example.com', 200);

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('異なるdeletionsのCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', 'john.doe@example.com', 100, 50);
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', 'john.doe@example.com', 100, 100);

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('nullのauthorNameと値がある場合のCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', null);
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe');

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('nullのauthorEmailと値がある場合のCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', null);
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit', '2024-01-01 12:00:00', 'John Doe', 'john.doe@example.com');

        expect($commit1->equals($commit2))->toBeFalse();
    });

    test('空文字列のmessageと値がある場合のCommitエンティティは等価でない', function () {
        $commit1 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', '');
        $commit2 = createCommit(123, 'main', 'a1b2c3d4e5f6789012345678901234567890abcd', 'Initial commit');

        expect($commit1->equals($commit2))->toBeFalse();
    });
});
