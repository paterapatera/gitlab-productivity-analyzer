<?php

namespace App\Infrastructure\GitLab;

use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;

trait FetchesBranches
{
    use HandlesGitLabApiRequests;

    /**
     * ブランチが存在しないかどうかをチェック
     */
    private static function isBranchNotFound(\Illuminate\Http\Client\Response $response): bool
    {
        return $response->status() === 404;
    }

    /**
     * ブランチの存在を検証
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     *
     * @throws GitLabApiException ブランチが存在しない場合、またはAPIエラー
     */
    protected function fetchBranchValidation(ProjectId $projectId, BranchName $branchName): void
    {
        $encodedBranchName = rawurlencode($branchName->value);
        $response = $this->makeGitLabRequest('get', "/api/v4/projects/{$projectId->value}/repository/branches/{$encodedBranchName}");

        if (self::isBranchNotFound($response)) {
            throw new GitLabApiException("Branch '{$branchName->value}' not found in project {$projectId->value}");
        }

        $this->checkApiError($response);
    }
}
