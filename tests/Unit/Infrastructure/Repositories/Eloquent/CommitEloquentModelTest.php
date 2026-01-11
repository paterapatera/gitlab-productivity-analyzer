<?php

use App\Infrastructure\Repositories\Eloquent\CommitEloquentModel;

test('CommitEloquentModelはEloquentモデルである', function () {
    $model = new CommitEloquentModel;

    expect($model)->toBeInstanceOf(\Illuminate\Database\Eloquent\Model::class);
});

test('CommitEloquentModelのテーブル名はcommitsである', function () {
    $model = new CommitEloquentModel;

    expect($model->getTable())->toBe('commits');
});

test('CommitEloquentModelはタイムスタンプを使用する', function () {
    $model = new CommitEloquentModel;

    expect($model->usesTimestamps())->toBeTrue();
});

test('CommitEloquentModelの一括代入可能な属性が定義されている', function () {
    $model = new CommitEloquentModel;

    $fillable = $model->getFillable();

    expect($fillable)->toContain('project_id');
    expect($fillable)->toContain('branch_name');
    expect($fillable)->toContain('sha');
    expect($fillable)->toContain('message');
    expect($fillable)->toContain('committed_date');
    expect($fillable)->toContain('author_name');
    expect($fillable)->toContain('author_email');
    expect($fillable)->toContain('additions');
    expect($fillable)->toContain('deletions');
});

test('CommitEloquentModelの属性キャストが定義されている', function () {
    $model = new CommitEloquentModel;

    $casts = $model->getCasts();

    expect($casts)->toHaveKey('committed_date');
    expect($casts['committed_date'])->toBe('datetime');
    expect($casts)->toHaveKey('additions');
    expect($casts['additions'])->toBe('integer');
    expect($casts)->toHaveKey('deletions');
    expect($casts['deletions'])->toBe('integer');
});

test('CommitEloquentModelは複合プライマリキーに対応している', function () {
    $model = new CommitEloquentModel;

    // 複合プライマリキーの場合、getKeyName()は最初のキーを返す
    // または、getKey()をオーバーライドして複合キーを返す
    expect($model->getKeyName())->toBe('project_id');
});
