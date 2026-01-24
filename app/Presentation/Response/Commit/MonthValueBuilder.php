<?php

namespace App\Presentation\Response\Commit;

class MonthValueBuilder
{
    /**
     * 月ごとの値を構築
     *
     * @param  array<string, mixed>  $agg
     * @return array<string, int>
     */
    public static function getMonthValue(array $agg): array
    {
        return ['additions' => $agg['total_additions'], 'deletions' => $agg['total_deletions']];
    }

    /**
     * 月ごとの合計を計算
     *
     * @param  array<int, array<string, int>>  $monthValues
     */
    public static function calculateMonthTotal(array $monthValues, int $month): int
    {
        $value = self::getMonthValueOrDefault($monthValues, $month);

        return $value['additions'] + $value['deletions'];
    }

    /**
     * 月の値を取得（デフォルト値付き）
     *
     * @param  array<int, array<string, int>>  $monthValues
     */
    public static function getMonthValueOrDefault(array $monthValues, int $month): array
    {
        return $monthValues[$month] ?? ['additions' => 0, 'deletions' => 0];
    }

    /**
     * 月ごとの合計データを構築
     *
     * @param  array<int, array<string, int>>  $monthValues
     * @param  array<int>  $months
     * @return array<int, int>
     */
    public static function buildMonthTotals(array $monthValues, array $months): array
    {
        /** @phpstan-ignore-next-line */
        return collect($months)->mapWithKeys(self::getMonthTotalMapper($monthValues))->toArray();
    }

    /**
     * 月の合計マッパーを取得
     *
     * @param  array<int, array<string, int>>  $monthValues
     */
    public static function getMonthTotalMapper(array $monthValues): callable
    {
        return fn (int $month) => self::buildMonthTotalPair($month, $monthValues);
    }

    /**
     * 月ごとの合計ペアを構築
     *
     * @param  array<int, array<string, int>>  $monthValues
     */
    public static function buildMonthTotalPair(int $month, array $monthValues): array
    {
        return [$month => self::calculateMonthTotal($monthValues, $month)];
    }
}
