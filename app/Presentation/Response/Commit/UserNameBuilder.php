<?php

namespace App\Presentation\Response\Commit;

class UserNameBuilder
{
    /**
     * ユーザー名のリストを構築
     *
     * @param  array<int, array<string, mixed>>  $aggregations
     * @return array<int, string>
     */
    public static function buildUserNames(array $aggregations): array
    {
        return collect($aggregations)
            ->pluck('author_name')
            ->map(UserMonthDataBuilder::normalizeAuthorName(...))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
