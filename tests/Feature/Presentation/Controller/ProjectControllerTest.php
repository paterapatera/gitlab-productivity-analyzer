<?php

use App\Application\Contract\GetProjects;
use App\Application\Contract\SyncProjects;
use App\Application\Port\ProjectRepository;
use Inertia\Testing\AssertableInertia as Assert;

// テストヘルパー関数
function getRepository(): ProjectRepository
{
    return app(ProjectRepository::class);
}

function mockSyncProjects(): void
{
    $mockSyncProjects = Mockery::mock(SyncProjects::class);
    app()->instance(SyncProjects::class, $mockSyncProjects);
}

describe('ProjectController', function () {
    describe('index()メソッド', function () {
        beforeEach(function () {
            mockSyncProjects();
        });
        test('プロジェクト一覧を取得してInertia.jsページを返却する', function () {
            $repository = getRepository();
            $repository->save(createProject(1, 'group/project1'));
            $repository->save(createProject(2, 'group/project2'));

            $response = $this->withoutVite()->get('/projects');

            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page
                ->component('Project/Index')
                ->has('projects', 2)
                ->where('projects.0.id', 1)
                ->where('projects.0.name_with_namespace', 'group/project1')
                ->where('projects.1.id', 2)
                ->where('projects.1.name_with_namespace', 'group/project2')
            );
        });

        test('空のプロジェクト一覧を返却できる', function () {
            $response = $this->withoutVite()->get('/projects');

            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page
                ->component('Project/Index')
                ->has('projects', 0)
            );
        });

        test('リポジトリエラー時に500エラーを返す', function () {
            $mockRepository = Mockery::mock(ProjectRepository::class);
            $mockRepository->shouldReceive('findAll')
                ->once()
                ->andThrow(new \Exception('Database connection failed'));

            app()->instance(ProjectRepository::class, $mockRepository);

            $response = $this->withoutVite()->get('/projects');

            $response->assertStatus(500);
        });
    });

    describe('sync()メソッド', function () {
        test('同期リクエストを処理してリダイレクトする', function () {
            // SyncProjectsは実際のインスタンスを使用（GetProjectsをモック）
            $mockGetProjects = Mockery::mock(GetProjects::class);
            $mockGetProjects->shouldReceive('execute')
                ->once()
                ->andReturn(collect([
                    createProject(1, 'group/project1'),
                    createProject(2, 'group/project2'),
                ]));

            app()->instance(GetProjects::class, $mockGetProjects);

            $repository = getRepository();
            $repository->save(createProject(3, 'group/project3'));

            $response = $this->post('/projects/sync');

            $response->assertStatus(302);
            $response->assertRedirect('/projects');

            $allProjects = $repository->findAll();
            expect($allProjects)->toHaveCount(2);
            expect($allProjects->pluck('id.value')->toArray())->toContain(1, 2);
            expect($allProjects->pluck('id.value')->toArray())->not->toContain(3);
        });

        test('同期エラー時にリダイレクトしてエラーメッセージを返す', function () {
            $mockGetProjects = Mockery::mock(GetProjects::class);
            $mockGetProjects->shouldReceive('execute')
                ->once()
                ->andThrow(new \Exception('GitLab APIへの接続に失敗しました。'));

            app()->instance(GetProjects::class, $mockGetProjects);

            $response = $this->post('/projects/sync');

            $response->assertStatus(302);
            $response->assertRedirect('/projects');
            $response->assertSessionHas('error');
        });

        test('同期処理エラー時にリダイレクトしてエラーメッセージを返す', function () {
            $mockGetProjects = Mockery::mock(GetProjects::class);
            $mockGetProjects->shouldReceive('execute')
                ->once()
                ->andThrow(new \RuntimeException('GitLab API connection failed'));

            app()->instance(GetProjects::class, $mockGetProjects);

            $response = $this->post('/projects/sync');

            $response->assertStatus(302);
            $response->assertRedirect('/projects');
            $response->assertSessionHas('error');
        });
    });
});
