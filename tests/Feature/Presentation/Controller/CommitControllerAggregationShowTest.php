<?php

use App\Application\Port\CommitUserMonthlyAggregationRepository;
use App\Domain\CommitUserMonthlyAggregation;
use App\Domain\ValueObjects\Additions;
use App\Domain\ValueObjects\AggregationMonth;
use App\Domain\ValueObjects\AggregationYear;
use App\Domain\ValueObjects\AuthorEmail;
use App\Domain\ValueObjects\AuthorName;
use App\Domain\ValueObjects\BranchName;
use App\Domain\ValueObjects\CommitCount;
use App\Domain\ValueObjects\CommitUserMonthlyAggregationId;
use App\Domain\ValueObjects\Deletions;
use App\Domain\ValueObjects\ProjectId;
use Inertia\Testing\AssertableInertia as Assert;

require_once __DIR__.'/../../Infrastructure/Helpers.php';
require_once __DIR__.'/../../Application/Helpers.php';
require_once __DIR__.'/../../../Unit/Domain/CommitCollectionHistoryTest.php';

describe('CommitController::aggregationShow()', function () {
    test('集計画面を表示してプロジェクト一覧、ブランチ一覧、年一覧を含む', function () {
        // プロジェクトを作成
        $projectRepository = getProjectRepository();
        $project1 = createProject(1, 'group/project1');
        $project2 = createProject(2, 'group/project2');
        $projectRepository->save($project1);
        $projectRepository->save($project2);

        // 収集履歴を作成（ブランチ一覧の取得に使用）
        $historyRepository = getCommitCollectionHistoryRepository();
        $history1 = createCommitCollectionHistory(1, 'main', '2024-01-01 12:00:00');
        $history2 = createCommitCollectionHistory(1, 'develop', '2024-02-01 12:00:00');
        $history3 = createCommitCollectionHistory(2, 'main', '2024-03-01 12:00:00');
        $historyRepository->save($history1);
        $historyRepository->save($history2);
        $historyRepository->save($history3);

        // 集計データを作成（年一覧の取得に使用）
        $aggregationRepository = app(CommitUserMonthlyAggregationRepository::class);
        $aggregation1 = new CommitUserMonthlyAggregation(
            id: new CommitUserMonthlyAggregationId(
                projectId: new ProjectId(1),
                branchName: new BranchName('main'),
                authorEmail: new AuthorEmail('john@example.com'),
                year: new AggregationYear(2024),
                month: new AggregationMonth(1)
            ),
            totalAdditions: new Additions(100),
            totalDeletions: new Deletions(50),
            commitCount: new CommitCount(5),
            authorName: new AuthorName('John Doe')
        );
        $aggregation2 = new CommitUserMonthlyAggregation(
            id: new CommitUserMonthlyAggregationId(
                projectId: new ProjectId(1),
                branchName: new BranchName('main'),
                authorEmail: new AuthorEmail('john@example.com'),
                year: new AggregationYear(2023),
                month: new AggregationMonth(12)
            ),
            totalAdditions: new Additions(200),
            totalDeletions: new Deletions(100),
            commitCount: new CommitCount(10),
            authorName: new AuthorName('John Doe')
        );
        $aggregationRepository->save($aggregation1);
        $aggregationRepository->save($aggregation2);

        $response = $this->withoutVite()->get('/commits/aggregation');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Commit/Aggregation')
            ->has('projects', 2)
            ->has('branches', 3)
            ->has('years', 0) // プロジェクト・ブランチが選択されていない場合は空
        );
    });

    test('選択されたプロジェクト・ブランチ・年の集計データを取得する', function () {
        // プロジェクトを作成
        $projectRepository = getProjectRepository();
        $project = createProject(1, 'group/project1');
        $projectRepository->save($project);

        // 収集履歴を作成
        $historyRepository = getCommitCollectionHistoryRepository();
        $history = createCommitCollectionHistory(1, 'main', '2024-01-01 12:00:00');
        $historyRepository->save($history);

        // 集計データを作成
        $aggregationRepository = app(CommitUserMonthlyAggregationRepository::class);
        $aggregation1 = new CommitUserMonthlyAggregation(
            id: new CommitUserMonthlyAggregationId(
                projectId: new ProjectId(1),
                branchName: new BranchName('main'),
                authorEmail: new AuthorEmail('john@example.com'),
                year: new AggregationYear(2024),
                month: new AggregationMonth(1)
            ),
            totalAdditions: new Additions(100),
            totalDeletions: new Deletions(50),
            commitCount: new CommitCount(5),
            authorName: new AuthorName('John Doe')
        );
        $aggregation2 = new CommitUserMonthlyAggregation(
            id: new CommitUserMonthlyAggregationId(
                projectId: new ProjectId(1),
                branchName: new BranchName('main'),
                authorEmail: new AuthorEmail('jane@example.com'),
                year: new AggregationYear(2024),
                month: new AggregationMonth(2)
            ),
            totalAdditions: new Additions(200),
            totalDeletions: new Deletions(100),
            commitCount: new CommitCount(10),
            authorName: new AuthorName('Jane Doe')
        );
        $aggregationRepository->save($aggregation1);
        $aggregationRepository->save($aggregation2);

        $response = $this->withoutVite()->get('/commits/aggregation?project_id=1&branch_name=main&year=2024');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Commit/Aggregation')
            ->has('aggregations', 2)
            ->where('aggregations.0.author_email', 'jane@example.com')
            ->where('aggregations.0.year', 2024)
            ->where('aggregations.0.month', 2)
            ->where('aggregations.1.author_email', 'john@example.com')
            ->where('aggregations.1.year', 2024)
            ->where('aggregations.1.month', 1)
        );
    });

    test('パラメータが指定されていない場合、集計データは空配列を返す', function () {
        // プロジェクトを作成
        $projectRepository = getProjectRepository();
        $project = createProject(1, 'group/project1');
        $projectRepository->save($project);

        $response = $this->withoutVite()->get('/commits/aggregation');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Commit/Aggregation')
            ->has('aggregations', 0)
        );
    });

    test('ブランチが選択されているが年が選択されていない場合、集計データは空配列を返す', function () {
        // プロジェクトを作成
        $projectRepository = getProjectRepository();
        $project = createProject(1, 'group/project1');
        $projectRepository->save($project);

        // 収集履歴を作成
        $historyRepository = getCommitCollectionHistoryRepository();
        $history = createCommitCollectionHistory(1, 'main', '2024-01-01 12:00:00');
        $historyRepository->save($history);

        // 集計データを作成
        $aggregationRepository = app(CommitUserMonthlyAggregationRepository::class);
        $aggregation = new CommitUserMonthlyAggregation(
            id: new CommitUserMonthlyAggregationId(
                projectId: new ProjectId(1),
                branchName: new BranchName('main'),
                authorEmail: new AuthorEmail('john@example.com'),
                year: new AggregationYear(2024),
                month: new AggregationMonth(1)
            ),
            totalAdditions: new Additions(100),
            totalDeletions: new Deletions(50),
            commitCount: new CommitCount(5),
            authorName: new AuthorName('John Doe')
        );
        $aggregationRepository->save($aggregation);

        $response = $this->withoutVite()->get('/commits/aggregation?project_id=1&branch_name=main');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Commit/Aggregation')
            ->has('aggregations', 0) // 年が選択されていないため、データは空
        );
    });

    test('フラッシュメッセージをpropsに追加できる', function () {
        $response = $this->withoutVite()
            ->withSession(['success' => '集計が完了しました。'])
            ->get('/commits/aggregation');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Commit/Aggregation')
            ->where('success', '集計が完了しました。')
        );
    });
});
