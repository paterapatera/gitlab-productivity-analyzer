<?php

namespace App\Presentation\Request\Commit;

use App\Presentation\Request\BaseRequest;

class RecollectRequest extends BaseRequest
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
}
