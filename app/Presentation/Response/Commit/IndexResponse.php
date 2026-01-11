<?php

namespace App\Presentation\Response\Commit;

use App\Application\DTO\CollectCommitsResult;
use App\Presentation\Response\ConvertsProjectsToArray;
use Illuminate\Support\Collection;

class IndexResponse
{
    use ConvertsProjectsToArray;

    /**
     * @param  Collection<int, \App\Domain\Project>  $projects
     */
    public function __construct(
        private readonly Collection $projects,
        private readonly ?CollectCommitsResult $result = null
    ) {}

    /**
     * Inertia.jsに渡すための配列に変換
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'projects' => $this->projectsToArray($this->projects, ['id', 'name_with_namespace']),
            'result' => $this->result ? [
                'collectedCount' => $this->result->collectedCount,
                'savedCount' => $this->result->savedCount,
                'hasErrors' => $this->result->hasErrors,
                'errorMessage' => $this->result->errorMessage,
            ] : null,
        ];
    }
}
