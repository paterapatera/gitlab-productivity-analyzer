<?php

use App\Application\DTO\CollectCommitsResult;
use App\Presentation\Response\Commit\IndexResponse;

test('IndexResponseはプロジェクトコレクションと結果から配列に変換できる', function () {
    $projects = collect([
        createProject(1, 'group/project1'),
        createProject(2, 'group/project2'),
    ]);

    $result = new CollectCommitsResult(
        collectedCount: 10,
        savedCount: 10,
        hasErrors: false,
        errorMessage: null
    );

    $response = new IndexResponse($projects, $result);
    $data = $response->toArray();

    expect($data)->toBeArray();
    expect($data)->toHaveKey('projects');
    expect($data)->toHaveKey('result');
    expect($data['projects'])->toBeArray();
    expect($data['projects'])->toHaveCount(2);
    expect($data['projects'][0])->toHaveKeys(['id', 'name_with_namespace']);
    expect($data['projects'][0]['id'])->toBe(1);
    expect($data['projects'][0]['name_with_namespace'])->toBe('group/project1');
    expect($data['result'])->toBeArray();
    expect($data['result'])->toHaveKeys(['collectedCount', 'savedCount', 'hasErrors', 'errorMessage']);
    expect($data['result']['collectedCount'])->toBe(10);
    expect($data['result']['savedCount'])->toBe(10);
    expect($data['result']['hasErrors'])->toBeFalse();
    expect($data['result']['errorMessage'])->toBeNull();
});

test('IndexResponseは空のコレクションを処理できる', function () {
    $projects = collect([]);
    $result = new CollectCommitsResult(
        collectedCount: 0,
        savedCount: 0,
        hasErrors: false,
        errorMessage: null
    );

    $response = new IndexResponse($projects, $result);
    $data = $response->toArray();

    expect($data)->toBeArray();
    expect($data)->toHaveKey('projects');
    expect($data)->toHaveKey('result');
    expect($data['projects'])->toBeArray();
    expect($data['projects'])->toBeEmpty();
    expect($data['result']['collectedCount'])->toBe(0);
});

test('IndexResponseはエラー結果を処理できる', function () {
    $projects = collect([
        createProject(1, 'group/project1'),
    ]);

    $result = new CollectCommitsResult(
        collectedCount: 0,
        savedCount: 0,
        hasErrors: true,
        errorMessage: 'ブランチが存在しません'
    );

    $response = new IndexResponse($projects, $result);
    $data = $response->toArray();

    expect($data['result']['hasErrors'])->toBeTrue();
    expect($data['result']['errorMessage'])->toBe('ブランチが存在しません');
});

test('IndexResponseは結果がnullの場合にresultをnullとして返す', function () {
    $projects = collect([
        createProject(1, 'group/project1'),
    ]);

    $response = new IndexResponse($projects, null);
    $data = $response->toArray();

    expect($data)->toBeArray();
    expect($data)->toHaveKey('projects');
    expect($data)->toHaveKey('result');
    expect($data['result'])->toBeNull();
});
