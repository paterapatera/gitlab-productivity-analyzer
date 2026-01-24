<?php

namespace App\Presentation\Response\Commit;

use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\Project;

class AggregationUtils
{
    /**
     * 選択が有効かどうかを判定
     */
    public static function isSelectionValid(?int $selectedProjectId, ?string $selectedBranchName): bool
    {
        return $selectedProjectId !== null && $selectedBranchName !== null;
    }

    /**
     * ブランチが選択されたものに一致するか
     *
     * @param  array<string, mixed>  $branch
     */
    public static function isBranchMatching(array $branch, ?int $selectedProjectId, ?string $selectedBranchName): bool
    {
        return $branch['project_id'] === $selectedProjectId
          && $branch['branch_name'] === $selectedBranchName;
    }

    /**
     * ブランチ選択用のコールバックを取得
     */
    public static function getBranchSelector(?int $selectedProjectId, ?string $selectedBranchName): callable
    {
        return fn (array $branch) => self::isBranchMatching($branch, $selectedProjectId, $selectedBranchName);
    }

    /**
     * 集計データを比較
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    public static function areProjectIdsDifferent(array $a, array $b): bool
    {
        return $a['project_id'] !== $b['project_id'];
    }

    /**
     * プロジェクトIDで比較
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    public static function getComparisonByProjectId(array $a, array $b): int
    {
        return $a['project_id'] <=> $b['project_id'];
    }

    /**
     * ブランチ名が異なるか
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    public static function areBranchNamesDifferent(array $a, array $b): bool
    {
        return $a['branch_name'] !== $b['branch_name'];
    }

    /**
     * ブランチ名で比較
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    public static function getComparisonByBranchName(array $a, array $b): int
    {
        return strcmp($a['branch_name'], $b['branch_name']);
    }

    /**
     * 著者名で比較
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    public static function compareByAuthorName(array $a, array $b): int
    {
        $authorNameA = $a['author_name'] ?? 'Unknown';
        $authorNameB = $b['author_name'] ?? 'Unknown';

        return strcmp($authorNameA, $authorNameB);
    }

    /**
     * ブランチ名で比較
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    public static function compareByBranchName(array $a, array $b): int
    {
        $different = self::areBranchNamesDifferent($a, $b);
        if ($different) {
            return self::getComparisonByBranchName($a, $b);
        } else {
            return self::compareByAuthorName($a, $b);
        }
    }

    /**
     * 集計データを比較
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    public static function compareAggregations(array $a, array $b): int
    {
        $different = self::areProjectIdsDifferent($a, $b);
        if ($different) {
            return self::getComparisonByProjectId($a, $b);
        } else {
            return self::compareByBranchName($a, $b);
        }
    }

    /**
     * 集計データを配列にマッピング
     *
     * @return array<string, mixed>
     */
    public static function mapAggregationToArray(CommitUserMonthlyAggregation $aggregation): array
    {
        return [
            'project_id' => $aggregation->id->projectId->value,
            'branch_name' => $aggregation->id->branchName->value,
            'author_email' => $aggregation->id->authorEmail->value,
            'author_name' => $aggregation->authorName->value,
            'year' => $aggregation->id->year->value,
            'month' => $aggregation->id->month->value,
            'total_additions' => $aggregation->totalAdditions->value,
            'total_deletions' => $aggregation->totalDeletions->value,
            'commit_count' => $aggregation->commitCount->value,
        ];
    }

    /**
     * プロジェクトを配列にマッピング
     *
     * @return array<string, mixed>
     */
    public static function mapProjectToArray(Project $project): array
    {
        return [
            'id' => $project->id->value,
            'name_with_namespace' => $project->nameWithNamespace->value,
        ];
    }
}
