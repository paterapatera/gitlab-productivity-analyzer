<?php

namespace App\Presentation\Response\Commit;

use App\Domain\CommitCollectionHistory;
use App\Domain\Project;
use Illuminate\Support\Collection;

class RecollectResponse
{
    /**
     * @param  Collection<int, CommitCollectionHistory>  $histories
     * @param  Collection<int, Project>  $projects
     */
    public function __construct(
        private readonly Collection $histories,
        private readonly Collection $projects
    ) {}

    /**
     * Inertia.jsに渡すための配列に変換
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        // プロジェクトIDをキーとしたマップを作成
        $projectMap = $this->projects->keyBy(fn (Project $project) => $project->id->value);

        // 履歴を配列に変換し、プロジェクト名を追加
        $historiesArray = $this->histories->map(function (CommitCollectionHistory $history) use ($projectMap) {
            $project = $projectMap->get($history->id->projectId->value);

            return [
                'project_id' => $history->id->projectId->value,
                'project_name_with_namespace' => $project?->nameWithNamespace->value ?? 'Unknown',
                'branch_name' => $history->id->branchName->value,
                'latest_committed_date' => $history->latestCommittedDate->value->format('c'), // ISO 8601
            ];
        })->toArray();

        return [
            'histories' => $historiesArray,
        ];
    }
}
