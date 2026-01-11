<?php

namespace App\Presentation\Response\Project;

use App\Presentation\Response\ConvertsProjectsToArray;
use Illuminate\Support\Collection;

class ListResponse
{
    use ConvertsProjectsToArray;

    /**
     * @param  Collection<int, \App\Domain\Project>  $projects
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
            'projects' => $this->projectsToArray($this->projects),
        ];
    }
}
