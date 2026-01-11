<?php

namespace App\Presentation\Request\Commit;

use App\Presentation\Request\BaseRequest;

class AggregationShowRequest extends BaseRequest
{
    /**
     * バリデーションルールを取得
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    /**
     * プロジェクトIDを取得
     */
    public function getProjectId(): ?int
    {
        $projectId = $this->request->input('project_id');
        if ($projectId === null || $projectId === '') {
            return null;
        }

        return (int) $projectId;
    }

    /**
     * ブランチ名を取得
     */
    public function getBranchName(): ?string
    {
        $branchName = $this->request->input('branch_name');
        if ($branchName === null || $branchName === '') {
            return null;
        }

        return (string) $branchName;
    }

    /**
     * 年を取得
     */
    public function getYear(): ?int
    {
        $year = $this->request->input('year');
        if ($year === null || $year === '') {
            return null;
        }

        return (int) $year;
    }
}
