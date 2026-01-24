<?php

namespace App\Presentation\Response\Commit;

use Illuminate\Support\Collection;

class ChartDataBuilder
{
    /**
     * 月データ構築リデューサーを取得
     */
    public static function getMonthDataReducer(int $month): callable
    {
        return fn ($monthData, $data) => self::addUserDataToMonthData($monthData, $data, $month);
    }

    /**
     * 月データを構築
     *
     * @param  Collection<string, array<string, mixed>>  $userMonthData
     */
    public static function buildMonthData(int $month, Collection $userMonthData): array
    {
        return $userMonthData->reduce(self::getMonthDataReducer($month), ['month' => sprintf('%d月', $month)]);
    }

    /**
     * 月データにユーザーデータを追加
     *
     * @param  array<string, mixed>  $monthData
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function addUserDataToMonthData(array $monthData, array $data, int $month): array
    {
        $userName = $data['userName'];
        $monthValue = self::getMonthValueFromData($data, $month);
        $monthData[sprintf('%s_additions', $userName)] = $monthValue['additions'];
        $monthData[sprintf('%s_deletions', $userName)] = $monthValue['deletions'];

        return $monthData;
    }

    /**
     * データから月の値を取得
     *
     * @param  array<string, mixed>  $data
     * @return array<string, int>
     */
    public static function getMonthValueFromData(array $data, int $month): array
    {
        return $data['monthValues']->get($month, ['additions' => 0, 'deletions' => 0]);
    }

    /**
     * グラフ用データを構築
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    public static function buildChartData(array $aggregations): array
    {
        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $userMonthData = collect($aggregations)
            ->groupBy(UserKeyBuilder::getUserKey(...))
            ->map(UserMonthDataBuilder::buildUserMonthData(...));

        return collect($months)
            ->map(self::getBuildMonthDataMapper($userMonthData))
            ->toArray();
    }

    /**
     * 月データ構築マッパーを取得
     *
     * @param  Collection<string, array<string, mixed>>  $userMonthData
     */
    public static function getBuildMonthDataMapper(Collection $userMonthData): callable
    {
        return fn ($month) => self::buildMonthData($month, $userMonthData);
    }
}
