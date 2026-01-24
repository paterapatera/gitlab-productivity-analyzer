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
        return $this->request->input('since_date')
        |> self::selectSinceDate(...);
    }

    /**
     * since_date が空かどうかを判定
     *
     * @param  string|null  $sinceDate
     */
    private static function isSinceDateEmpty($sinceDate): bool
    {
        return $sinceDate === null || $sinceDate === '';
    }

    /**
     * since_date を選択して DateTime に変換
     *
     * @param  string|null  $sinceDate
     */
    private static function selectSinceDate($sinceDate): ?\DateTime
    {
        if (self::isSinceDateEmpty($sinceDate)) {
            return null;
        } else {
            assert(is_string($sinceDate));

            return self::parseDateTime($sinceDate);
        }
    }

    private static function parseDateTime(string $dateString): ?\DateTime
    {
        try {
            return new \DateTime($dateString);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
