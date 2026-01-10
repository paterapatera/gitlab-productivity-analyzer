<?php

use App\Presentation\Request\Project\ListRequest;
use Illuminate\Http\Request;

test('ListRequestはHTTPリクエストから作成できる', function () {
    $httpRequest = Request::create('/projects', 'GET');

    $request = new ListRequest($httpRequest);

    expect($request->getRequest())->toBe($httpRequest);
});

test('ListRequestはバリデーションルールを返す', function () {
    $httpRequest = Request::create('/projects', 'GET');
    $request = new ListRequest($httpRequest);

    $rules = $request->rules();

    expect($rules)->toBeArray();
    // GETリクエストなので、現時点ではバリデーションルールは空
    expect($rules)->toBeEmpty();
});
