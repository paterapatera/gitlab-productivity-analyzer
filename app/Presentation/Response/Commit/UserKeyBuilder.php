<?php

namespace App\Presentation\Response\Commit;

class UserKeyBuilder
{
    /**
     * ユーザーキーを取得
     *
     * @param  array<string, mixed>  $agg
     */
    public static function getUserKey(array $agg): string
    {
        return sprintf('%d-%s-%s', $agg['project_id'], $agg['branch_name'], $agg['author_email']);
    }
}
