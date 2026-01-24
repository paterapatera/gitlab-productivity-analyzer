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
     * 選択されたブランチを検索
     *
     * @return array{project_id: int, branch_name: string}|null
     */
    private function findSelectedBranch(): ?array
    {
        return $this->branches->first(AggregationUtils::getBranchSelector($this->selectedProjectId, $this->selectedBranchName));
    }

    /**
     * 選択されたブランチ情報を取得
     *
     * @return array{project_id: int, branch_name: string}|null
     */
    private function getSelectedBranch(): ?array
    {
        if (AggregationUtils::isSelectionValid($this->selectedProjectId, $this->selectedBranchName)) {
            return $this->findSelectedBranch();
        } else {
            return null;
        }
    }

    /**
     * 集計データを処理
     *
     * @param  Collection<int, CommitUserMonthlyAggregation>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    private function processAggregations(Collection $aggregations): array
    {
        return $aggregations
            ->map(AggregationUtils::mapAggregationToArray(...))
            ->sort(AggregationUtils::compareAggregations(...))
            ->values()
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        // 集計データを配列に変換
        $aggregationsArray = $this->processAggregations($this->aggregations);

        // グラフ用データの準備
        $chartData = AggregationDataBuilder::buildChartData($aggregationsArray);

        // 表用データの準備
        $tableData = AggregationDataBuilder::buildTableData($aggregationsArray);

        // ユーザー名のリストを取得（凡例用）
        $userNames = AggregationDataBuilder::buildUserNames($aggregationsArray);

        return [
            'projects' => $this->projects->map(AggregationUtils::mapProjectToArray(...))->toArray(),
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
}
