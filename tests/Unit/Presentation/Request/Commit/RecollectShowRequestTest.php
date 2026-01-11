<?php

use App\Presentation\Request\Commit\RecollectShowRequest;
use Illuminate\Http\Request;

test('RecollectShowRequestはHTTPリクエストから作成できる', function () {
    $httpRequest = Request::create('/commits/recollect', 'GET');

    $request = new RecollectShowRequest($httpRequest);

    expect($request->getRequest())->toBe($httpRequest);
});

test('RecollectShowRequestはバリデーションルールを返す', function () {
    $httpRequest = Request::create('/commits/recollect', 'GET');
    $request = new RecollectShowRequest($httpRequest);

    $rules = $request->rules();

    expect($rules)->toBeArray();
    // GETリクエストなので、現時点ではバリデーションルールは空
    expect($rules)->toBeEmpty();
});
