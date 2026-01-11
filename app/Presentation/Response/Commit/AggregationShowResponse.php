<?php

namespace App\Presentation\Response\Commit;

use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\Project;
use Illuminate\Support\Collection;

class AggregationShowResponse
{
    /**
     * @param  Collection<int, Project>  $projects
     * @param  Collection<int, array{project_id: int, branch_name: string}>  $branches
     * @param  Collection<int, int>  $years
     * @param  Collection<int, CommitUserMonthlyAggregation>  $aggregations
     */
    public function __construct(
        private readonly Collection $projects,
        private readonly Collection $branches,
        private readonly Collection $years,
        private readonly Collection $aggregations,
        private readonly ?int $selectedProjectId = null,
        private readonly ?string $selectedBranchName = null,
        private readonly ?int $selectedYear = null
    ) {}

    /**
     * 選択されたブランチ情報を取得
     *
     * @return array{project_id: int, branch_name: string}|null
     */
    private function getSelectedBranch(): ?array
    {
        if ($this->selectedProjectId === null || $this->selectedBranchName === null) {
            return null;
        }

        return $this->branches->first(function ($branch) {
            return $branch['project_id'] === $this->selectedProjectId
                && $branch['branch_name'] === $this->selectedBranchName;
        });
    }

    /**
     * Inertia.jsに渡すための配列に変換
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        // 集計データを配列に変換
        $aggregationsArray = $this->aggregations->map(function (CommitUserMonthlyAggregation $aggregation) {
            return [
                'project_id' => $aggregation->id->projectId->value,
                'branch_name' => $aggregation->id->branchName->value,
                'author_email' => $aggregation->id->authorEmail->value,
                'author_name' => $aggregation->authorName->value,
                'year' => $aggregation->id->year->value,
                'month' => $aggregation->id->month->value,
                'total_additions' => $aggregation->totalAdditions->value,
                'total_deletions' => $aggregation->totalDeletions->value,
                'commit_count' => $aggregation->commitCount->value,
            ];
        })
            // プロジェクトID、ブランチ名、ユーザーの昇順でソート（要件5.10）
            ->sort(function ($a, $b) {
                if ($a['project_id'] !== $b['project_id']) {
                    return $a['project_id'] <=> $b['project_id'];
                }
                if ($a['branch_name'] !== $b['branch_name']) {
                    return strcmp($a['branch_name'], $b['branch_name']);
                }
                $authorNameA = $a['author_name'] ?? 'Unknown';
                $authorNameB = $b['author_name'] ?? 'Unknown';

                return strcmp($authorNameA, $authorNameB);
            })
            ->values()
            ->toArray();

        // グラフ用データの準備
        $chartData = $this->buildChartData($aggregationsArray);

        // 表用データの準備
        $tableData = $this->buildTableData($aggregationsArray);

        // ユーザー名のリストを取得（凡例用）
        $userNames = $this->buildUserNames($aggregationsArray);

        return [
            'projects' => $this->projects->map(function (Project $project) {
                return [
                    'id' => $project->id->value,
                    'name_with_namespace' => $project->nameWithNamespace->value,
                ];
            })->toArray(),
            'branches' => $this->branches->toArray(),
            'years' => $this->years->toArray(),
            'aggregations' => $aggregationsArray,
            'chartData' => $chartData,
            'tableData' => $tableData,
            'userNames' => $userNames,
            'selectedProjectId' => $this->selectedProjectId,
            'selectedBranchName' => $this->selectedBranchName,
            'selectedYear' => $this->selectedYear,
            'selectedBranch' => $this->getSelectedBranch(),
        ];
    }

    /**
     * グラフ用データを構築
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    private function buildChartData(array $aggregations): array
    {
        // ユーザーごと、月ごとにグループ化
        $userMonthData = [];
        $userInfoMap = []; // ユーザーキー => ユーザー名のマップ

        foreach ($aggregations as $agg) {
            $userKey = sprintf('%d-%s-%s', $agg['project_id'], $agg['branch_name'], $agg['author_email']);
            if (! isset($userMonthData[$userKey])) {
                $userMonthData[$userKey] = [];
                $userInfoMap[$userKey] = $agg['author_name'] ?? 'Unknown';
            }
            $userMonthData[$userKey][$agg['month']] = [
                'additions' => $agg['total_additions'],
                'deletions' => $agg['total_deletions'],
            ];
        }

        // グラフ用データ配列を作成（月ごと、ユーザーごと）
        $chartData = [];
        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        foreach ($months as $month) {
            $monthData = ['month' => sprintf('%d月', $month)];

            foreach ($userMonthData as $userKey => $monthValues) {
                $userName = $userInfoMap[$userKey];
                $monthValue = $monthValues[$month] ?? ['additions' => 0, 'deletions' => 0];

                // ユーザーごとの追加行数と削除行数を設定
                $monthData[sprintf('%s_additions', $userName)] = $monthValue['additions'];
                $monthData[sprintf('%s_deletions', $userName)] = $monthValue['deletions'];
            }

            $chartData[] = $monthData;
        }

        return $chartData;
    }

    /**
     * 表用データを構築
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    private function buildTableData(array $aggregations): array
    {
        // ユーザーごと、月ごとにグループ化
        $userMonthData = [];
        $userInfoMap = []; // ユーザーキー => ユーザー名のマップ

        foreach ($aggregations as $agg) {
            $userKey = sprintf('%d-%s-%s', $agg['project_id'], $agg['branch_name'], $agg['author_email']);
            if (! isset($userMonthData[$userKey])) {
                $userMonthData[$userKey] = [];
                $userInfoMap[$userKey] = $agg['author_name'] ?? 'Unknown';
            }
            $userMonthData[$userKey][$agg['month']] = [
                'additions' => $agg['total_additions'],
                'deletions' => $agg['total_deletions'],
            ];
        }

        // 表用データの準備（ユーザーごと、月ごと）
        $tableData = [];
        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        foreach ($userMonthData as $userKey => $monthValues) {
            $monthTotals = [];
            foreach ($months as $month) {
                $monthValue = $monthValues[$month] ?? ['additions' => 0, 'deletions' => 0];
                $monthTotals[$month] = $monthValue['additions'] + $monthValue['deletions']; // 合計行数
            }

            $tableData[] = [
                'userKey' => $userKey,
                'userName' => $userInfoMap[$userKey],
                'months' => $monthTotals,
            ];
        }

        return $tableData;
    }

    /**
     * ユーザー名のリストを構築
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, string>
     */
    private function buildUserNames(array $aggregations): array
    {
        $userNames = [];
        foreach ($aggregations as $agg) {
            $userName = $agg['author_name'] ?? 'Unknown';
            if (! in_array($userName, $userNames, true)) {
                $userNames[] = $userName;
            }
        }
        sort($userNames);

        return $userNames;
    }
}
