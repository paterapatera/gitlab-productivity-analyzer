<?php

namespace App\Infrastructure\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $project_id
 * @property string $branch_name
 * @property string $author_email
 * @property int $year
 * @property int $month
 * @property string|null $author_name
 * @property int $total_additions
 * @property int $total_deletions
 * @property int $commit_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CommitUserMonthlyAggregationEloquentModel extends Model
{
    /**
     * テーブル名
     */
    protected $table = 'commit_user_monthly_aggregations';

    /**
     * プライマリキー（複合プライマリキーの最初のキー）
     */
    protected $primaryKey = 'project_id';

    /**
     * プライマリキーの型
     */
    protected $keyType = 'int';

    /**
     * プライマリキーがインクリメントされるか
     */
    public $incrementing = false;

    /**
     * タイムスタンプを使用する
     */
    public $timestamps = true;

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'project_id',
        'branch_name',
        'author_email',
        'year',
        'month',
        'author_name',
        'total_additions',
        'total_deletions',
        'commit_count',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'project_id' => 'integer',
        'year' => 'integer',
        'month' => 'integer',
        'total_additions' => 'integer',
        'total_deletions' => 'integer',
        'commit_count' => 'integer',
    ];
}
