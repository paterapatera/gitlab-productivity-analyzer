<?php

namespace App\Presentation\Request\Commit;

use App\Presentation\Request\BaseRequest;

class CollectRequest extends BaseRequest
{
    /**
     * バリデーションルールを取得
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'branch_name' => ['required', 'string', 'max:255'],
            'since_date' => ['nullable', 'date'],
        ];
    }

    /**
     * プロジェクトIDを取得
     */
    public function getProjectId(): int
    {
        return (int) $this->request->input('project_id');
    }

    /**
     * ブランチ名を取得
     */
    public function getBranchName(): string
    {
        return (string) $this->request->input('branch_name');
    }

    /**
     * 開始日を取得（オプショナル）
     */
    public function getSinceDate(): ?\DateTime
    {
        $sinceDate = $this->request->input('since_date');
        if ($sinceDate === null || $sinceDate === '') {
            return null;
        }

        try {
            return new \DateTime($sinceDate);
        } catch (\Exception $e) {
            return null;
        }
    }
}
