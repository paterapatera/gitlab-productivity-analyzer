<?php

namespace App\Presentation\Response\Commit;

use Illuminate\Support\Collection;

class TableDataBuilder
{
    /**
     * 表行構築コールバックを取得
     *
     * @param  array<int>  $months
     */
    public static function getTableRowBuilder(array $months): callable
    {
        return fn ($group) => [
            'userKey' => UserKeyBuilder::getUserKey($group->first()),
            'userName' => UserMonthDataBuilder::normalizeAuthorName($group->first()['author_name']),
            'months' => MonthValueBuilder::buildMonthTotals($group->keyBy('month')->map(MonthValueBuilder::getMonthValue(...))->toArray(), $months),
        ];
    }

    /**
     * 表の行データを構築
     *
     * @param  Collection<int, array<string, mixed>>  $group
     * @param  array<int>  $months
     */
    public static function buildTableRow(Collection $group, array $months): array
    {
        return $group->pipe(self::getTableRowBuilder($months));
    }

    /**
     * 表用データを構築
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    public static function buildTableData(array $aggregations): array
    {
        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        return collect($aggregations)
            ->groupBy(UserKeyBuilder::getUserKey(...))
            ->map(self::getTableRowBuilder($months))
            ->values()
            ->toArray();
    }
}
