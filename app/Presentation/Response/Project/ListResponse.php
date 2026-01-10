<?php

namespace App\Presentation\Response\Project;

use App\Domain\Project;
use Illuminate\Support\Collection;

class ListResponse
{
    /**
     * @param  Collection<int, Project>  $projects
     */
    public function __construct(
        private readonly Collection $projects
    ) {}

    /**
     * Inertia.jsに渡すための配列に変換
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'projects' => $this->projects->map(function (Project $project) {
                return [
                    'id' => $project->id->value,
                    'name_with_namespace' => $project->nameWithNamespace->value,
                    'description' => $project->description->value,
                    'default_branch' => $project->defaultBranch->value,
                ];
            })->toArray(),
        ];
    }
}
