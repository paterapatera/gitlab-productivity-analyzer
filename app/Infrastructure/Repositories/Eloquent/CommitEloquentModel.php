<?php

namespace App\Infrastructure\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $project_id
 * @property string $branch_name
 * @property string $sha
 * @property string|null $message
 * @property Carbon $committed_date
 * @property string|null $author_name
 * @property string|null $author_email
 * @property int $additions
 * @property int $deletions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CommitEloquentModel extends Model
{
    /**
     * テーブル名
     */
    protected $table = 'commits';

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
        'sha',
        'message',
        'committed_date',
        'author_name',
        'author_email',
        'additions',
        'deletions',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'project_id' => 'integer',
        'committed_date' => 'datetime',
        'additions' => 'integer',
        'deletions' => 'integer',
    ];
}
