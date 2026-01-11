<?php

namespace App\Infrastructure\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $project_id
 * @property string $branch_name
 * @property Carbon $latest_committed_date
 * @property-read ProjectEloquentModel $project
 */
class CommitCollectionHistoryEloquentModel extends Model
{
    /**
     * テーブル名
     */
    protected $table = 'commit_collection_histories';

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
     * タイムスタンプを使用しない
     */
    public $timestamps = false;

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'project_id',
        'branch_name',
        'latest_committed_date',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'project_id' => 'integer',
        'latest_committed_date' => 'datetime',
    ];

    /**
     * プロジェクトへのリレーションシップ
     *
     * @return BelongsTo<ProjectEloquentModel, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectEloquentModel::class, 'project_id', 'id');
    }
}
