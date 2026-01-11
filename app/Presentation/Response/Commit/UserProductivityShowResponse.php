<?php

namespace App\Presentation\Response\Commit;

use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\UserInfo;
use Illuminate\Support\Collection;

class UserProductivityShowResponse
{
    /**
     * @param  Collection<int, UserInfo>  $users
     * @param  Collection<int, int>  $years
     * @param  Collection<int, CommitUserMonthlyAggregation>  $aggregations
     * @param  array<string>|null  $selectedUsers
     */
    public function __construct(
        private readonly Collection $users,
        private readonly Collection $years,
        private readonly Collection $aggregations,
        private readonly ?int $selectedYear = null,
        private readonly ?array $selectedUsers = null
    ) {}

    /**
     * Inertia.jsに渡すための配列に変換
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        // 集計データを配列に変換
        $aggregationsArray = $this->aggregations->map(function (CommitUserMonthlyAggregation $aggregation) {
            return [
                'author_email' => $aggregation->id->authorEmail->value,
                'author_name' => $aggregation->authorName->value,
                'year' => $aggregation->id->year->value,
                'month' => $aggregation->id->month->value,
                'total_additions' => $aggregation->totalAdditions->value,
                'total_deletions' => $aggregation->totalDeletions->value,
                'commit_count' => $aggregation->commitCount->value,
            ];
        })->toArray();

        // グラフ用データの準備
        $chartData = $this->buildChartData($aggregationsArray);

        // 表用データの準備
        $tableData = $this->buildTableData($aggregationsArray);

        // ユーザー名のリストを取得（凡例用）
        $userNames = $this->buildUserNames($aggregationsArray);

        return [
            'users' => $this->users->map(function (UserInfo $user) {
                return [
                    'author_email' => $user->email->value,
                    'author_name' => $user->name->value,
                ];
            })->toArray(),
            'years' => $this->years->toArray(),
            'chartData' => $chartData,
            'tableData' => $tableData,
            'userNames' => $userNames,
            'selectedYear' => $this->selectedYear,
            'selectedUsers' => $this->selectedUsers,
        ];
    }

    /**
     * グラフ用データを構築
     * 複数リポジトリにまたがる同一ユーザーのデータを統合（月ごとにtotal_additions、total_deletionsを合計）
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    private function buildChartData(array $aggregations): array
    {
        // リポジトリで統合されたユーザー情報から、メールアドレス => 名前のマップを作成
        $userInfoMap = [];
        foreach ($this->users as $user) {
            assert($user->email->value !== null);
            $userInfoMap[$user->email->value] = $user->name->value ?: 'Unknown';
        }

        // ユーザーごと（author_email）、月ごとにグループ化して統合
        $userMonthData = [];

        foreach ($aggregations as $agg) {
            $authorEmail = $agg['author_email'];

            if (! isset($userMonthData[$authorEmail])) {
                $userMonthData[$authorEmail] = [];
            }

            $month = $agg['month'];
            if (! isset($userMonthData[$authorEmail][$month])) {
                $userMonthData[$authorEmail][$month] = [
                    'additions' => 0,
                    'deletions' => 0,
                ];
            }

            // 複数リポジトリのデータを統合（合計）
            $userMonthData[$authorEmail][$month]['additions'] += $agg['total_additions'];
            $userMonthData[$authorEmail][$month]['deletions'] += $agg['total_deletions'];
        }

        // グラフ用データ配列を作成（月ごと、ユーザーごと）
        $chartData = [];
        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        foreach ($months as $month) {
            $monthData = ['month' => sprintf('%d月', $month)];

            foreach ($userMonthData as $authorEmail => $monthValues) {
                $userName = $userInfoMap[$authorEmail] ?? 'Unknown';
                $monthValue = $monthValues[$month] ?? ['additions' => 0, 'deletions' => 0];

                // ユーザーごとの追加行数と削除行数を設定（積み上げグラフ用）
                $monthData[sprintf('%s_additions', $userName)] = $monthValue['additions'];
                $monthData[sprintf('%s_deletions', $userName)] = $monthValue['deletions'];
            }

            $chartData[] = $monthData;
        }

        return $chartData;
    }

    /**
     * 表用データを構築
     * 複数リポジトリにまたがる同一ユーザーのデータを統合（月ごとに合計行数を計算）
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    private function buildTableData(array $aggregations): array
    {
        // リポジトリで統合されたユーザー情報から、メールアドレス => 名前のマップを作成
        $userInfoMap = [];
        foreach ($this->users as $user) {
            assert($user->email->value !== null);
            $userInfoMap[$user->email->value] = $user->name->value ?: 'Unknown';
        }

        // ユーザーごと（author_email）、月ごとにグループ化して統合
        $userMonthData = [];

        foreach ($aggregations as $agg) {
            $authorEmail = $agg['author_email'];

            if (! isset($userMonthData[$authorEmail])) {
                $userMonthData[$authorEmail] = [];
            }

            $month = $agg['month'];
            if (! isset($userMonthData[$authorEmail][$month])) {
                $userMonthData[$authorEmail][$month] = [
                    'additions' => 0,
                    'deletions' => 0,
                ];
            }

            // 複数リポジトリのデータを統合（合計）
            $userMonthData[$authorEmail][$month]['additions'] += $agg['total_additions'];
            $userMonthData[$authorEmail][$month]['deletions'] += $agg['total_deletions'];
        }

        // 表用データの準備（ユーザーごと、月ごと）
        $tableData = [];
        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        foreach ($userMonthData as $authorEmail => $monthValues) {
            $monthTotals = [];
            foreach ($months as $month) {
                $monthValue = $monthValues[$month] ?? ['additions' => 0, 'deletions' => 0];
                $monthTotals[$month] = $monthValue['additions'] + $monthValue['deletions']; // 合計行数
            }

            $tableData[] = [
                'userKey' => $authorEmail,
                'userName' => $userInfoMap[$authorEmail] ?? 'Unknown',
                'months' => $monthTotals,
            ];
        }

        return $tableData;
    }

    /**
     * ユーザー名のリストを構築
     * リポジトリで統合されたユーザー情報から取得
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, string>
     */
    private function buildUserNames(array $aggregations): array
    {
        // 集計データに含まれるメールアドレスのセットを作成
        $emailSet = [];
        foreach ($aggregations as $agg) {
            $emailSet[$agg['author_email']] = true;
        }

        // リポジトリで統合されたユーザー情報から、集計データに含まれるユーザーの名前を取得
        $userNames = [];
        foreach ($this->users as $user) {
            if (isset($emailSet[$user->email->value ?? ''])) {
                $userName = $user->name->value ?: 'Unknown';
                if (! in_array($userName, $userNames, true)) {
                    $userNames[] = $userName;
                }
            }
        }
        sort($userNames);

        return $userNames;
    }
}
