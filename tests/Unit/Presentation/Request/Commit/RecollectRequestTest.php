<?php

use App\Presentation\Request\Commit\RecollectRequest;
use Illuminate\Http\Request;

test('RecollectRequestはHTTPリクエストから作成できる', function () {
    $httpRequest = Request::create('/commits/recollect', 'POST', [
        'project_id' => 1,
        'branch_name' => 'main',
    ]);

    $request = new RecollectRequest($httpRequest);

    expect($request->getRequest())->toBe($httpRequest);
});

test('RecollectRequestはバリデーションルールを返す', function () {
    $httpRequest = Request::create('/commits/recollect', 'POST', [
        'project_id' => 1,
        'branch_name' => 'main',
    ]);
    $request = new RecollectRequest($httpRequest);

    $rules = $request->rules();

    expect($rules)->toBeArray();
    expect($rules)->toHaveKey('project_id');
    expect($rules)->toHaveKey('branch_name');
    expect($rules['project_id'])->toContain('required', 'integer', 'exists:projects,id');
    expect($rules['branch_name'])->toContain('required', 'string', 'max:255');
});

test('RecollectRequestはプロジェクトIDを取得できる', function () {
    $httpRequest = Request::create('/commits/recollect', 'POST', [
        'project_id' => 123,
        'branch_name' => 'main',
    ]);
    $request = new RecollectRequest($httpRequest);

    $projectId = $request->getProjectId();

    expect($projectId)->toBe(123);
    expect($projectId)->toBeInt();
});

test('RecollectRequestはブランチ名を取得できる', function () {
    $httpRequest = Request::create('/commits/recollect', 'POST', [
        'project_id' => 1,
        'branch_name' => 'develop',
    ]);
    $request = new RecollectRequest($httpRequest);

    $branchName = $request->getBranchName();

    expect($branchName)->toBe('develop');
    expect($branchName)->toBeString();
});

test('RecollectRequestは文字列のプロジェクトIDを整数に変換する', function () {
    $httpRequest = Request::create('/commits/recollect', 'POST', [
        'project_id' => '456',
        'branch_name' => 'main',
    ]);
    $request = new RecollectRequest($httpRequest);

    $projectId = $request->getProjectId();

    expect($projectId)->toBe(456);
    expect($projectId)->toBeInt();
});
