<?php

namespace App\Presentation\Response\Commit;

class AggregationDataBuilder
{
    /**
     * グラフ用データを構築
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    public static function buildChartData(array $aggregations): array
    {
        return ChartDataBuilder::buildChartData($aggregations);
    }

    /**
     * 表用データを構築
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    public static function buildTableData(array $aggregations): array
    {
        return TableDataBuilder::buildTableData($aggregations);
    }

    /**
     * ユーザー名のリストを構築
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, string>
     */
    public static function buildUserNames(array $aggregations): array
    {
        return UserNameBuilder::buildUserNames($aggregations);
    }
}
