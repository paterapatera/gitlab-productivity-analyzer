<?php

use App\Presentation\Response\Project\ListResponse;

test('ListResponseはプロジェクトコレクションから配列に変換できる', function () {
    $projects = collect([
        createProject(1, 'group/project1'),
        createProject(2, 'group/project2'),
    ]);

    $response = new ListResponse($projects);
    $data = $response->toArray();

    expect($data)->toBeArray();
    expect($data)->toHaveKey('projects');
    expect($data['projects'])->toBeArray();
    expect($data['projects'])->toHaveCount(2);
    expect($data['projects'][0])->toHaveKeys(['id', 'name_with_namespace', 'description', 'default_branch']);
    expect($data['projects'][0]['id'])->toBe(1);
    expect($data['projects'][0]['name_with_namespace'])->toBe('group/project1');
    expect($data['projects'][0]['description'])->toBeNull();
    expect($data['projects'][0]['default_branch'])->toBeNull();
});

test('ListResponseは空のコレクションを処理できる', function () {
    $projects = collect([]);

    $response = new ListResponse($projects);
    $data = $response->toArray();

    expect($data)->toBeArray();
    expect($data)->toHaveKey('projects');
    expect($data['projects'])->toBeArray();
    expect($data['projects'])->toBeEmpty();
});

test('ListResponseはdescriptionとdefaultBranchを含むプロジェクトを変換できる', function () {
    $projects = collect([
        createProject(1, 'group/project1', 'Description 1', 'main'),
    ]);

    $response = new ListResponse($projects);
    $data = $response->toArray();

    expect($data['projects'][0]['description'])->toBe('Description 1');
    expect($data['projects'][0]['default_branch'])->toBe('main');
});
