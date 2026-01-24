<?php

namespace App\Application\Service;

use App\Application\Contract\AggregateCommits;
use App\Application\Contract\CollectCommits as CollectCommitsInterface;
use App\Application\DTO\CollectCommitsResult;
use App\Application\Port\CommitCollectionHistoryRepository;
use App\Application\Port\CommitRepository;
use App\Application\Port\GitApi;
use App\Application\Port\ProjectRepository;
use App\Domain\CommitCollectionHistory;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitCollectionHistoryId;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\GitLab\Exceptions\GitLabApiException;
use Illuminate\Support\Facades\Log;

/**
 * コミットを収集・永続化するサービス
 */
class CollectCommits extends BaseService implements CollectCommitsInterface
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly GitApi $gitApi,
        private readonly CommitRepository $commitRepository,
        private readonly CommitCollectionHistoryRepository $commitCollectionHistoryRepository,
        private readonly AggregateCommits $aggregateCommits
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
        return $this->executeWithOverallErrorHandling(function (&$collectedCount) use ($projectId, $branchName, $sinceDate) {
            // プロジェクト存在検証
            $project = $this->projectRepository->findByProjectId($projectId);
            if ($project === null) {
                return $this->createErrorResult('プロジェクトが存在しません');
            }

            // ブランチ存在検証
            $this->gitApi->validateBranch($projectId, $branchName);

            // sinceDateがnullの場合、収集履歴から最新日時を取得して自動判定
            $sinceDate = $this->determineSinceDate($projectId, $branchName, $sinceDate);

            // コミット取得
            $commits = $this->gitApi->getCommits($projectId, $branchName, $sinceDate);
            $collectedCount = $commits->count();

            // コミット永続化と収集履歴の記録を同一トランザクションで実行
            $this->transaction(function () use ($commits, $projectId, $branchName) {
                $this->commitRepository->saveMany($commits);

                // コミットが収集された場合、収集履歴を記録
                if ($commits->isNotEmpty()) {
                    // 収集したコミットの最新日時を取得（committedDateで降順ソートして最初の要素を取得）
                    $latestCommit = $commits->sortByDesc(fn ($commit) => $commit->committedDate->value->getTimestamp())->first();
                    if ($latestCommit !== null) {
                        // 収集履歴を作成または更新
                        $historyId = new CommitCollectionHistoryId($projectId, $branchName);
                        $history = new CommitCollectionHistory(
                            id: $historyId,
                            latestCommittedDate: $latestCommit->committedDate
                        );
                        $this->commitCollectionHistoryRepository->save($history);
                    }
                }
            });

            // コミット保存完了後に集計処理を実行
            // エラー時はログに記録するのみで、CollectCommitsResultには影響を与えない
            $this->executeAggregationWithLogging($projectId, $branchName);

            return new CollectCommitsResult(
                collectedCount: $collectedCount,
                savedCount: $collectedCount,
                hasErrors: false
            );
        });
    }

    private function executeWithOverallErrorHandling(callable $callback): CollectCommitsResult
    {
        $collectedCount = 0;
        try {
            return $callback($collectedCount);
        } catch (GitLabApiException $e) {
            return $this->createErrorResult($e->getMessage());
        } catch (\Exception $e) {
            // コミット取得後のエラー（保存エラーなど）の場合、収集数は記録する
            return $this->createErrorResult($e->getMessage(), $collectedCount);
        }
    }

    private function determineSinceDate(ProjectId $projectId, BranchName $branchName, ?\DateTime $sinceDate): ?\DateTime
    {
        if ($sinceDate !== null) {
            return $sinceDate;
        }

        try {
            $historyId = new CommitCollectionHistoryId($projectId, $branchName);
            $history = $this->commitCollectionHistoryRepository->findById($historyId);

            return $history?->latestCommittedDate->value;
        } catch (\Exception $e) {
            // エラーが発生した場合、フォールバック動作として全コミットを収集
            return null;
        }
    }

    private function executeAggregationWithLogging(ProjectId $projectId, BranchName $branchName): void
    {
        try {
            $this->aggregateCommits->execute($projectId, $branchName);
        } catch (\Exception $e) {
            Log::error('集計処理でエラーが発生しました', [
                'project_id' => $projectId->value,
                'branch_name' => $branchName->value,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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
