<?php

namespace App\Infrastructure\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectEloquentModel extends Model
{
    use SoftDeletes;
    /**
     * テーブル名
     */
    protected $table = 'projects';

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
        'id',
        'description',
        'name_with_namespace',
        'default_branch',
        'deleted_at',
    ];

    /**
     * 属性のキャスト
     */
    protected $casts = [
        'id' => 'integer',
        'deleted_at' => 'datetime',
    ];
}
