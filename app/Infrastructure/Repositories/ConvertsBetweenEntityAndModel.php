<?php

namespace App\Infrastructure\Repositories;

use Illuminate\Support\Collection;

/**
 * エンティティとEloquentモデルの変換を提供するトレイト
 *
 * このトレイトは、リポジトリの save() と saveMany() メソッドの共通実装を提供します。
 * エンティティとモデルの変換ロジックは、各リポジトリで実装する必要があります。
 *
 * @template TEntity エンティティの型
 * @template TModel Eloquentモデルの型
 */
trait ConvertsBetweenEntityAndModel
{
    /**
     * エンティティを保存または更新
     *
     * @param  TEntity  $entity  保存するエンティティ
     * @return TEntity 保存されたエンティティ
     */
    protected function saveEntity($entity)
    {
        $model = $this->findModel($entity);

        if ($model === null) {
            $model = $this->createModel($entity);
        }

        $this->updateModelFromEntity($model, $entity);
        $model->save();

        return $this->toEntity($model);
    }

    /**
     * 複数のエンティティを一括保存または更新
     *
     * @param  Collection<int, TEntity>  $entities  保存するエンティティのコレクション
     */
    protected function saveManyEntities(Collection $entities): void
    {
        $entities->each(fn ($entity) => $this->saveEntity($entity));
    }

    /**
     * エンティティに対応するEloquentモデルを検索
     *
     * @param  TEntity  $entity  検索対象のエンティティ
     * @return TModel|null 見つかったモデル、見つからない場合は null
     */
    abstract protected function findModel($entity);

    /**
     * エンティティから新しいEloquentモデルを作成
     *
     * @param  TEntity  $entity  エンティティ
     * @return TModel 作成されたモデル
     */
    abstract protected function createModel($entity);

    /**
     * Eloquentモデルをエンティティに変換
     *
     * @param  TModel  $model  Eloquentモデル
     * @return TEntity 変換されたエンティティ
     */
    abstract protected function toEntity($model);

    /**
     * エンティティからEloquentモデルを更新
     *
     * @param  TModel  $model  更新対象のEloquentモデル
     * @param  TEntity  $entity  エンティティ
     */
    abstract protected function updateModelFromEntity($model, $entity): void;
}
