<?php

namespace App\Application\Service;

use App\Application\Contract\CollectCommits as CollectCommitsInterface;
use App\Application\DTO\CollectCommitsResult;
use App\Application\Port\CommitRepository;
use App\Application\Port\GitApi;
use App\Application\Port\ProjectRepository;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;

/**
 * コミットを収集・永続化するサービス
 */
class CollectCommits extends BaseService implements CollectCommitsInterface
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly GitApi $gitApi,
        private readonly CommitRepository $commitRepository
    ) {}

    /**
     * プロジェクトとブランチを指定してコミットを収集・永続化
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @param  \DateTime|null  $sinceDate  開始日（オプショナル、指定された場合はこの日以降のコミットのみを収集）
     * @return CollectCommitsResult 収集結果
     */
    public function execute(
        ProjectId $projectId,
        BranchName $branchName,
        ?\DateTime $sinceDate = null
    ): CollectCommitsResult {
        $collectedCount = 0;

        try {
            // プロジェクト存在検証
            $project = $this->projectRepository->findByProjectId($projectId);
            if ($project === null) {
                return $this->createErrorResult('プロジェクトが存在しません');
            }

            // ブランチ存在検証
            $this->gitApi->validateBranch($projectId, $branchName);

            // コミット取得
            $commits = $this->gitApi->getCommits($projectId, $branchName, $sinceDate);
            $collectedCount = $commits->count();

            // コミット永続化
            $this->transaction(function () use ($commits) {
                $this->commitRepository->saveMany($commits);
            });

            return new CollectCommitsResult(
                collectedCount: $collectedCount,
                savedCount: $collectedCount,
                hasErrors: false
            );
        } catch (GitLabApiException $e) {
            return $this->createErrorResult($e->getMessage());
        } catch (\Exception $e) {
            // コミット取得後のエラー（保存エラーなど）の場合、収集数は記録する
            return $this->createErrorResult($e->getMessage(), $collectedCount);
        }
    }

    /**
     * エラー結果を作成
     *
     * @param  string  $errorMessage  エラーメッセージ
     * @param  int  $collectedCount  収集済みのコミット数（オプショナル、保存エラーなどで使用）
     * @return CollectCommitsResult エラー結果
     */
    protected function createErrorResult(string $errorMessage, int $collectedCount = 0): CollectCommitsResult
    {
        return new CollectCommitsResult(
            collectedCount: $collectedCount,
            savedCount: 0,
            hasErrors: true,
            errorMessage: $errorMessage
        );
    }
}
