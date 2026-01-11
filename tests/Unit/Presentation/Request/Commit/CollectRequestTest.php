<?php

use App\Presentation\Request\Commit\CollectRequest;
use Illuminate\Http\Request;

test('CollectRequestはHTTPリクエストから作成できる', function () {
    $httpRequest = Request::create('/commits/collect', 'POST', [
        'project_id' => 1,
        'branch_name' => 'main',
    ]);

    $request = new CollectRequest($httpRequest);

    expect($request->getRequest())->toBe($httpRequest);
});

test('CollectRequestはバリデーションルールを返す', function () {
    $httpRequest = Request::create('/commits/collect', 'POST');
    $request = new CollectRequest($httpRequest);

    $rules = $request->rules();

    expect($rules)->toBeArray();
    expect($rules)->toHaveKey('project_id');
    expect($rules)->toHaveKey('branch_name');
    expect($rules)->toHaveKey('since_date');
});

test('CollectRequestのproject_idルールはrequired、integer、existsである', function () {
    $httpRequest = Request::create('/commits/collect', 'POST');
    $request = new CollectRequest($httpRequest);

    $rules = $request->rules();

    expect($rules['project_id'])->toContain('required');
    expect($rules['project_id'])->toContain('integer');
    expect($rules['project_id'])->toContain('exists:projects,id');
});

test('CollectRequestのbranch_nameルールはrequired、string、max:255である', function () {
    $httpRequest = Request::create('/commits/collect', 'POST');
    $request = new CollectRequest($httpRequest);

    $rules = $request->rules();

    expect($rules['branch_name'])->toContain('required');
    expect($rules['branch_name'])->toContain('string');
    expect($rules['branch_name'])->toContain('max:255');
});

test('CollectRequestのsince_dateルールはnullable、dateである', function () {
    $httpRequest = Request::create('/commits/collect', 'POST');
    $request = new CollectRequest($httpRequest);

    $rules = $request->rules();

    expect($rules['since_date'])->toContain('nullable');
    expect($rules['since_date'])->toContain('date');
});

test('CollectRequestはプロジェクトIDを取得できる', function () {
    $httpRequest = Request::create('/commits/collect', 'POST', [
        'project_id' => 123,
    ]);

    $request = new CollectRequest($httpRequest);

    expect($request->getProjectId())->toBe(123);
});

test('CollectRequestはブランチ名を取得できる', function () {
    $httpRequest = Request::create('/commits/collect', 'POST', [
        'branch_name' => 'main',
    ]);

    $request = new CollectRequest($httpRequest);

    expect($request->getBranchName())->toBe('main');
});

test('CollectRequestは開始日を取得できる', function () {
    $httpRequest = Request::create('/commits/collect', 'POST', [
        'since_date' => '2025-01-01',
    ]);

    $request = new CollectRequest($httpRequest);
    $sinceDate = $request->getSinceDate();

    expect($sinceDate)->toBeInstanceOf(\DateTime::class);
    expect($sinceDate->format('Y-m-d'))->toBe('2025-01-01');
});

test('CollectRequestは開始日がnullの場合にnullを返す', function () {
    $httpRequest = Request::create('/commits/collect', 'POST', []);

    $request = new CollectRequest($httpRequest);

    expect($request->getSinceDate())->toBeNull();
});

test('CollectRequestは開始日が空文字列の場合にnullを返す', function () {
    $httpRequest = Request::create('/commits/collect', 'POST', [
        'since_date' => '',
    ]);

    $request = new CollectRequest($httpRequest);

    expect($request->getSinceDate())->toBeNull();
});

test('CollectRequestは無効な日付形式の場合にnullを返す', function () {
    $httpRequest = Request::create('/commits/collect', 'POST', [
        'since_date' => 'invalid-date',
    ]);

    $request = new CollectRequest($httpRequest);

    expect($request->getSinceDate())->toBeNull();
});
