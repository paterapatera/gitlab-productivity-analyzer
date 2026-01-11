<?php

use App\Application\Contract\CollectCommits;
use App\Application\Port\GitApi;
use App\Application\Port\ProjectRepository;
use App\Infrastructure\GitLab\GitLabApiClient;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

require_once __DIR__.'/../../Infrastructure/Helpers.php';

describe('CommitController', function () {
    describe('index()メソッド', function () {

        test('コミット収集ページを表示してプロジェクト一覧を含む', function () {
            $repository = getProjectRepository();
            $repository->save(createProject(1, 'group/project1'));
            $repository->save(createProject(2, 'group/project2'));

            $response = $this->withoutVite()->get('/commits/collect');

            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page
                ->component('Commit/Index')
                ->has('projects', 2)
                ->where('projects.0.id', 1)
                ->where('projects.0.name_with_namespace', 'group/project1')
                ->where('projects.1.id', 2)
                ->where('projects.1.name_with_namespace', 'group/project2')
                ->where('result', null)
            );
        });

        test('空のプロジェクト一覧を返却できる', function () {
            $response = $this->withoutVite()->get('/commits/collect');

            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page
                ->component('Commit/Index')
                ->has('projects', 0)
                ->where('result', null)
            );
        });

        test('フラッシュメッセージをpropsに追加できる', function () {
            $response = $this->withoutVite()
                ->withSession(['success' => 'コミット収集が完了しました。'])
                ->get('/commits/collect');

            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page
                ->component('Commit/Index')
                ->where('success', 'コミット収集が完了しました。')
            );
        });

        test('リポジトリエラー時に500エラーを返す', function () {
            $mockRepository = Mockery::mock(ProjectRepository::class);
            $mockRepository->shouldReceive('findAll')
                ->once()
                ->andThrow(new \Exception('Database connection failed'));

            $this->app->instance(ProjectRepository::class, $mockRepository);

            $response = $this->withoutVite()->get('/commits/collect');

            $response->assertStatus(500);
        });
    });

    describe('collect()メソッド', function () {
        test('コミット収集リクエストを処理してリダイレクトする', function () {
            $projectRepository = getProjectRepository();
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            // GitLab API をモック
            Http::fake(createCommitCollectionApiMock(
                projectId: 1,
                branchName: 'main',
                commits: [
                    createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2025-01-15T12:00:00Z', 'Author 1', 'author1@example.com', 10, 5),
                    createCommitData('b2c3d4e5f6789012345678901234567890abcdef', 'Commit 2', '2025-01-14T12:00:00Z', 'Author 2', 'author2@example.com', 20, 10),
                ]
            ));

            // GitApiを実際のインスタンスに置き換え（Http::fake()でモック済み）
            $gitApi = new GitLabApiClient('https://gitlab.example.com', 'test-token');
            app()->instance(GitApi::class, $gitApi);

            $response = $this->post('/commits/collect', [
                'project_id' => 1,
                'branch_name' => 'main',
            ]);

            $response->assertStatus(302);
            $response->assertRedirect('/commits/collect');
            $response->assertSessionHas('success', 'コミット収集が完了しました。収集: 2件、保存: 2件');

            // データベースにコミットが保存されていることを確認
            $commitRepository = getCommitRepository();
            // 実際のコミットが保存されているかは、CollectCommitsTestで確認済み
        });

        test('開始日パラメータを指定してコミット収集を実行できる', function () {
            $projectRepository = getProjectRepository();
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            // GitLab API をモック（開始日以降のコミットのみ）
            Http::fake(createCommitCollectionApiMock(
                projectId: 1,
                branchName: 'main',
                commits: [
                    createCommitData('a1b2c3d4e5f6789012345678901234567890abcd', 'Commit 1', '2025-01-15T12:00:00Z', 'Author 1', 'author1@example.com', 10, 5),
                ]
            ));

            // GitApiを実際のインスタンスに置き換え（Http::fake()でモック済み）
            $gitApi = new GitLabApiClient('https://gitlab.example.com', 'test-token');
            app()->instance(GitApi::class, $gitApi);

            $response = $this->post('/commits/collect', [
                'project_id' => 1,
                'branch_name' => 'main',
                'since_date' => '2025-01-01',
            ]);

            $response->assertStatus(302);
            $response->assertRedirect('/commits/collect');
            $response->assertSessionHas('success', 'コミット収集が完了しました。収集: 1件、保存: 1件');

            // sinceパラメータが送信されていることを確認
            Http::assertSent(function ($request) {
                return str_contains($request->url(), 'since=2025-01-01');
            });
        });

        test('エラー時にリダイレクトしてエラーメッセージを返す', function () {
            $projectRepository = getProjectRepository();
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            // GitLab API をモック（ブランチが存在しない）
            Http::fake(createCommitCollectionApiMock(
                projectId: 1,
                branchName: 'nonexistent',
                commits: [],
                branchExists: false
            ));

            // GitApiを実際のインスタンスに置き換え（Http::fake()でモック済み）
            $gitApi = new GitLabApiClient('https://gitlab.example.com', 'test-token');
            app()->instance(GitApi::class, $gitApi);

            $response = $this->post('/commits/collect', [
                'project_id' => 1,
                'branch_name' => 'nonexistent',
            ]);

            $response->assertStatus(302);
            $response->assertRedirect('/commits/collect');

            // エラーメッセージがセッションに正しく保存されていることを確認
            $response->assertSessionHas('error', function ($errorMessage) {
                // エラーメッセージが文字列で、空でないことを確認
                expect($errorMessage)->toBeString();
                expect($errorMessage)->not->toBeEmpty();

                // エラーメッセージにブランチ名とプロジェクトIDが含まれていることを確認
                expect($errorMessage)->toContain('not found');
                expect($errorMessage)->toContain('nonexistent');
                expect($errorMessage)->toContain('1');

                // 実際のエラーメッセージの形式を確認: "Branch 'nonexistent' not found in project 1"
                expect($errorMessage)->toMatch('/Branch.*not found.*project.*1/');

                return true;
            });
        });

        test('バリデーションエラー時にリダイレクトしてエラーを返す', function () {
            $response = $this->post('/commits/collect', [
                'project_id' => '',
                'branch_name' => '',
            ]);

            $response->assertStatus(302);
            $response->assertRedirect('/commits/collect');
            $response->assertSessionHasErrors(['project_id', 'branch_name']);
        });

        test('存在しないプロジェクトIDでバリデーションエラーを返す', function () {
            $response = $this->post('/commits/collect', [
                'project_id' => 999,
                'branch_name' => 'main',
            ]);

            $response->assertStatus(302);
            $response->assertRedirect('/commits/collect');
            $response->assertSessionHasErrors(['project_id']);
        });

        test('無効な日付形式でバリデーションエラーを返す', function () {
            $repository = getProjectRepository();
            $repository->save(createProject(1, 'group/project1'));

            $response = $this->post('/commits/collect', [
                'project_id' => 1,
                'branch_name' => 'main',
                'since_date' => 'invalid-date',
            ]);

            $response->assertStatus(302);
            $response->assertRedirect('/commits/collect');
            $response->assertSessionHasErrors(['since_date']);
        });

        test('例外発生時に500エラーを返す', function () {
            $projectRepository = getProjectRepository();
            $project = createProject(1, 'group/project1');
            $projectRepository->save($project);

            // CollectCommitsサービスをモックして例外をスロー
            $mockCollectCommits = Mockery::mock(CollectCommits::class);
            $mockCollectCommits->shouldReceive('execute')
                ->once()
                ->andThrow(new \Exception('Unexpected error'));

            app()->instance(CollectCommits::class, $mockCollectCommits);

            $response = $this->post('/commits/collect', [
                'project_id' => 1,
                'branch_name' => 'main',
            ]);

            $response->assertStatus(500);
        });
    });
});
