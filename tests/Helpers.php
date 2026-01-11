<?php

use App\Application\Port\CommitRepository;
use App\Application\Port\ProjectRepository;
use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\Project;
use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AggregationMonth;
use App\Domain\ValueObjects\AggregationYear;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitCount;
use App\Domain\ValueObjects\CommitUserMonthlyAggregationId;
use App\Domain\ValueObjects\DefaultBranch;
use App\Domain\ValueObjects\Deletions;
use App\Domain\ValueObjects\ProjectDescription;
use App\Domain\ValueObjects\ProjectId;
use App\Domain\ValueObjects\ProjectNameWithNamespace;

if (! function_exists('createProject')) {
    /**
     * テスト用のProjectエンティティを作成
     */
    function createProject(
        int $id = 123,
        string $nameWithNamespace = 'group/project',
        ?string $description = null,
        ?string $defaultBranch = null
    ): Project {
        return new Project(
            id: new ProjectId($id),
            nameWithNamespace: new ProjectNameWithNamespace($nameWithNamespace),
            description: $description !== null ? new ProjectDescription($description) : new ProjectDescription(null),
            defaultBranch: $defaultBranch !== null ? new DefaultBranch($defaultBranch) : new DefaultBranch(null)
        );
    }
}

if (! function_exists('getProjectRepository')) {
    /**
     * ProjectRepository のインスタンスを取得
     */
    function getProjectRepository(): ProjectRepository
    {
        return app(ProjectRepository::class);
    }
}

if (! function_exists('getCommitRepository')) {
    /**
     * CommitRepository のインスタンスを取得
     */
    function getCommitRepository(): CommitRepository
    {
        return app(CommitRepository::class);
    }
}

if (! function_exists('getCommitCollectionHistoryRepository')) {
    /**
     * CommitCollectionHistoryRepository のインスタンスを取得
     */
    function getCommitCollectionHistoryRepository(): \App\Application\Port\CommitCollectionHistoryRepository
    {
        return app(\App\Application\Port\CommitCollectionHistoryRepository::class);
    }
}

if (! function_exists('createCommitUserMonthlyAggregation')) {
    /**
     * テスト用のCommitUserMonthlyAggregationエンティティを作成
     */
    function createCommitUserMonthlyAggregation(
        int $projectId = 123,
        string $branchName = 'main',
        string $authorEmail = 'test@example.com',
        int $year = 2024,
        int $month = 1,
        ?string $authorName = 'John Doe',
        int $totalAdditions = 100,
        int $totalDeletions = 50,
        int $commitCount = 5
    ): CommitUserMonthlyAggregation {
        return new CommitUserMonthlyAggregation(
            id: new CommitUserMonthlyAggregationId(
                projectId: new ProjectId($projectId),
                branchName: new BranchName($branchName),
                authorEmail: new AuthorEmail($authorEmail),
                year: new AggregationYear($year),
                month: new AggregationMonth($month)
            ),
            totalAdditions: new Additions($totalAdditions),
            totalDeletions: new Deletions($totalDeletions),
            commitCount: new CommitCount($commitCount),
            authorName: new AuthorName($authorName)
        );
    }
}

// Application層のヘルパー関数を読み込む（getGitLabApiClientなど）
if (file_exists(__DIR__.'/Feature/Application/Helpers.php')) {
    require_once __DIR__.'/Feature/Application/Helpers.php';
}
