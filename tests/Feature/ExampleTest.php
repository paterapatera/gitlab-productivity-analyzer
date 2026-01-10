<?php

use Inertia\Testing\AssertableInertia as Assert;

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('renders example page on home route', function () {
    $response = $this->get('/');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('example')
    );
});

it('does not render welcome page on any route', function () {
    // ホームルートが welcome ページをレンダリングしないことを確認
    $response = $this->get('/');
    
    $response->assertInertia(fn (Assert $page) => $page
        ->component('example')
    );
    
    // welcome ページコンポーネントが存在しないことを確認
    $this->assertFileDoesNotExist(resource_path('js/pages/welcome.tsx'));
});

it('does not have dashboard page component', function () {
    // Dashboard ページコンポーネントが存在しないことを確認
    $this->assertFileDoesNotExist(resource_path('js/pages/dashboard.tsx'));
});

it('does not have dashboard route', function () {
    // Dashboard ルートが存在しないことを確認（404 を返す）
    $response = $this->get('/dashboard');
    
    $response->assertStatus(404);
});

it('verifies top page integration', function () {
    // タスク 6.1: トップページの動作を確認する
    // ルートパス（/）にアクセスして ExamplePage が正しくレンダリングされることを確認
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('example')
    );
    
    // ExamplePage コンポーネントが存在することを確認
    $this->assertFileExists(resource_path('js/pages/example.tsx'));
});

it('verifies dashboard removal', function () {
    // タスク 6.2: Dashboard 削除の確認を行う
    
    // 1. Dashboard ルートにアクセスして 404 エラーが返されることを確認
    $response = $this->get('/dashboard');
    $response->assertStatus(404);
    
    // 2. Dashboard ページコンポーネントが存在しないことを確認
    $this->assertFileDoesNotExist(resource_path('js/pages/dashboard.tsx'));
    
    // 3. AppSidebar と AppHeader が削除されていることを確認（トップページから使用されていないため）
    $this->assertFileDoesNotExist(resource_path('js/components/app-sidebar.tsx'));
    $this->assertFileDoesNotExist(resource_path('js/components/app-header.tsx'));
});
