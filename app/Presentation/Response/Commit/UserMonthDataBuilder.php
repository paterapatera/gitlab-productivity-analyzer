<?php

namespace App\Presentation\Response\Commit;

use Illuminate\Support\Collection;

class UserMonthDataBuilder
{
    /**
     * ユーザーの月データ構築コールバックを取得
     *
     * @param  Collection<int, array<string, mixed>>  $group
     * @return array<string, mixed>
     */
    public static function getUserMonthDataBuilder(Collection $group): array
    {
        $first = $group->first();

        return [
            'userName' => self::normalizeAuthorName($first['author_name'] ?? null),
            'monthValues' => $group->keyBy('month')->map(MonthValueBuilder::getMonthValue(...)),
        ];
    }

    /**
     * ユーザーの月データを構築
     *
     * @param  Collection<int, array<string, mixed>>  $group
     * @return array<string, mixed>
     */
    public static function buildUserMonthData(Collection $group): array
    {
        return $group->pipe(self::getUserMonthDataBuilder(...));
    }

    /**
     * 著者名を正規化
     */
    public static function normalizeAuthorName(?string $name): string
    {
        return self::getAuthorNameOrDefault($name);
    }

    /**
     * 著者名を取得（デフォルト値付き）
     */
    public static function getAuthorNameOrDefault(?string $name): string
    {
        return $name ?? 'Unknown';
    }
}
