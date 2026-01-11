<?php

use App\Presentation\Response\Commit\RecollectResponse;

test('RecollectResponseは収集履歴とプロジェクトから配列に変換できる', function () {
    $projects = collect([
        createProject(1, 'group/project1'),
        createProject(2, 'group/project2'),
    ]);

    $histories = collect([
        createCommitCollectionHistory(1, 'main', '2025-01-01 12:00:00'),
        createCommitCollectionHistory(2, 'develop', '2025-01-02 12:00:00'),
    ]);

    $response = new RecollectResponse($histories, $projects);
    $data = $response->toArray();

    expect($data)->toBeArray();
    expect($data)->toHaveKey('histories');
    expect($data['histories'])->toBeArray();
    expect($data['histories'])->toHaveCount(2);

    // 最初の履歴を確認
    expect($data['histories'][0])->toHaveKeys(['project_id', 'project_name_with_namespace', 'branch_name', 'latest_committed_date']);
    expect($data['histories'][0]['project_id'])->toBe(1);
    expect($data['histories'][0]['project_name_with_namespace'])->toBe('group/project1');
    expect($data['histories'][0]['branch_name'])->toBe('main');
    expect($data['histories'][0]['latest_committed_date'])->toMatch('/2025-01-01T12:00:00/');

    // 2番目の履歴を確認
    expect($data['histories'][1]['project_id'])->toBe(2);
    expect($data['histories'][1]['project_name_with_namespace'])->toBe('group/project2');
    expect($data['histories'][1]['branch_name'])->toBe('develop');
    expect($data['histories'][1]['latest_committed_date'])->toMatch('/2025-01-02T12:00:00/');
});

test('RecollectResponseは空のコレクションを処理できる', function () {
    $projects = collect([]);
    $histories = collect([]);

    $response = new RecollectResponse($histories, $projects);
    $data = $response->toArray();

    expect($data)->toBeArray();
    expect($data)->toHaveKey('histories');
    expect($data['histories'])->toBeArray();
    expect($data['histories'])->toBeEmpty();
});

test('RecollectResponseはプロジェクトが存在しない場合Unknownを表示する', function () {
    $projects = collect([
        createProject(1, 'group/project1'),
    ]);

    // プロジェクトID 2 の履歴を作成（プロジェクトは存在しない）
    $histories = collect([
        createCommitCollectionHistory(2, 'main', '2025-01-01 12:00:00'),
    ]);

    $response = new RecollectResponse($histories, $projects);
    $data = $response->toArray();

    expect($data['histories'][0]['project_id'])->toBe(2);
    expect($data['histories'][0]['project_name_with_namespace'])->toBe('Unknown');
    expect($data['histories'][0]['branch_name'])->toBe('main');
});

test('RecollectResponseはプロジェクトマップを使用して効率的に変換する', function () {
    $projects = collect([
        createProject(1, 'group/project1'),
        createProject(2, 'group/project2'),
        createProject(3, 'group/project3'),
    ]);

    // 同じプロジェクトの異なるブランチの履歴
    $histories = collect([
        createCommitCollectionHistory(1, 'main', '2025-01-01 12:00:00'),
        createCommitCollectionHistory(1, 'develop', '2025-01-02 12:00:00'),
        createCommitCollectionHistory(2, 'main', '2025-01-03 12:00:00'),
    ]);

    $response = new RecollectResponse($histories, $projects);
    $data = $response->toArray();

    expect($data['histories'])->toHaveCount(3);
    expect($data['histories'][0]['project_name_with_namespace'])->toBe('group/project1');
    expect($data['histories'][1]['project_name_with_namespace'])->toBe('group/project1');
    expect($data['histories'][2]['project_name_with_namespace'])->toBe('group/project2');
});
