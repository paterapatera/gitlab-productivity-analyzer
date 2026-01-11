<?php

use App\Application\Port\CommitRepository;
use App\Application\Port\ProjectRepository;
use App\Domain\Project;
use App\Domain\ValueObjects\DefaultBranch;
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
