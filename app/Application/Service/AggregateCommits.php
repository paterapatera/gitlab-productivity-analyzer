<?php

namespace App\Application\Service;

use App\Application\Contract\AggregateCommits as AggregateCommitsInterface;
use App\Application\DTO\AggregateCommitsResult;
use App\Application\Port\CommitRepository;
use App\Application\Port\CommitUserMonthlyAggregationRepository;
use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AggregationMonth;
use App\Domain\ValueObjects\AggregationYear;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitCount;
use App\Domain\ValueObjects\CommitUserMonthlyAggregationId;
use App\Domain\ValueObjects\Deletions;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * コミットデータから月次集計を生成するサービス
 */
class AggregateCommits extends BaseService implements AggregateCommitsInterface
{
    public function __construct(
        private readonly CommitRepository $commitRepository,
        private readonly CommitUserMonthlyAggregationRepository $aggregationRepository
    ) {}

    /**
     * プロジェクトとブランチを指定してコミットデータから月次集計を生成
     *
     * @param  ProjectId  $projectId  プロジェクトID
     * @param  BranchName  $branchName  ブランチ名
     * @return AggregateCommitsResult 集計結果
     */
    public function execute(
        ProjectId $projectId,
        BranchName $branchName
    ): AggregateCommitsResult {
        return $this->executeWithErrorHandling(function () use ($projectId, $branchName) {
            // 最終集計月を取得
            $latestAggregationMonth = $this->aggregationRepository->findLatestAggregationMonth(
                $projectId,
                $branchName
            );

            // 集計範囲を決定（最終集計月の翌月から先月まで）
            $now = Carbon::now();
            $lastMonth = $now->copy()->subMonth()->endOfMonth();

            if ($latestAggregationMonth !== null) {
                // 既存集計月がある場合、その翌月から開始
                $startDate = $latestAggregationMonth->copy()->addMonth()->startOfMonth();
            } else {
                // 集計データが存在しない場合、最初のコミットの月から開始
                // コミットが存在しない場合は空の結果を返す
                $earliestCommit = $this->commitRepository->findByProjectAndBranchAndDateRange(
                    $projectId,
                    $branchName,
                    new \DateTime('1970-01-01 00:00:00'),
                    $lastMonth->toDateTime()
                )->sortBy(fn ($commit) => $commit->committedDate->value->getTimestamp())->first();

                if ($earliestCommit === null) {
                    return new AggregateCommitsResult(aggregatedCount: 0);
                }

                $startDate = Carbon::instance($earliestCommit->committedDate->value)->startOfMonth();
            }

            // 先月までしか集計しない（今月は除外）
            if ($startDate->isAfter($lastMonth)) {
                return new AggregateCommitsResult(aggregatedCount: 0);
            }

            // 該当期間のコミットを取得
            $commits = $this->commitRepository->findByProjectAndBranchAndDateRange(
                $projectId,
                $branchName,
                $startDate->toDateTime(),
                $lastMonth->toDateTime()
            );

            if ($commits->isEmpty()) {
                return new AggregateCommitsResult(aggregatedCount: 0);
            }

            // コミットを年月とユーザーでグループ化して集計
            $aggregations = $this->aggregateCommitsByUserAndMonth($commits, $projectId, $branchName);

            // トランザクション内で集計データを保存
            $this->transaction(function () use ($aggregations) {
                $this->aggregationRepository->saveMany($aggregations);
            });

            return new AggregateCommitsResult(
                aggregatedCount: $aggregations->count(),
                hasErrors: false
            );
        });
    }

    private function executeWithErrorHandling(callable $callback): AggregateCommitsResult
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            return new AggregateCommitsResult(
                aggregatedCount: 0,
                hasErrors: true,
                errorMessage: $e->getMessage()
            );
        }
    }

    /**
     * コミットをユーザーと年月でグループ化して集計
     *
     * @param  Collection<int, \App\Domain\Commit>  $commits
     * @return Collection<int, CommitUserMonthlyAggregation>
     */
    private function aggregateCommitsByUserAndMonth(
        Collection $commits,
        ProjectId $projectId,
        BranchName $branchName
    ): Collection {
        // コミットをユーザー（author_email）と年月でグループ化
        $grouped = $commits->groupBy(function ($commit) {
            $date = Carbon::instance($commit->committedDate->value);
            $authorEmail = $commit->authorEmail->value ?? 'unknown@example.com';

            return sprintf('%s|%d|%d', $authorEmail, $date->year, $date->month);
        });

        $aggregations = collect();

        foreach ($grouped as $key => $groupedCommits) {
            [$authorEmail, $year, $month] = explode('|', $key);

            // 同一ユーザー・同一年月のコミットのadditionsとdeletionsを合計
            $totalAdditions = $groupedCommits->sum(fn ($commit) => $commit->additions->value);
            $totalDeletions = $groupedCommits->sum(fn ($commit) => $commit->deletions->value);
            $commitCount = $groupedCommits->count();

            // 同一ユーザーのコミットからauthor_nameを取得（最初の非null値を取得）
            $authorName = $groupedCommits->first(fn ($commit) => $commit->authorName->value !== null)?->authorName->value
                ?? null;

            $aggregation = new CommitUserMonthlyAggregation(
                id: new CommitUserMonthlyAggregationId(
                    projectId: $projectId,
                    branchName: $branchName,
                    authorEmail: new AuthorEmail($authorEmail),
                    year: new AggregationYear((int) $year),
                    month: new AggregationMonth((int) $month)
                ),
                totalAdditions: new Additions($totalAdditions),
                totalDeletions: new Deletions($totalDeletions),
                commitCount: new CommitCount($commitCount),
                authorName: new AuthorName($authorName)
            );

            $aggregations->push($aggregation);
        }

        return $aggregations;
    }
}
