<?php

use App\Application\Port\ProjectRepository;
use App\Domain\Project;
use App\Domain\ValueObjects\ProjectId;
use App\Infrastructure\Repositories\EloquentProjectRepository;
use Illuminate\Support\Collection;

test('EloquentProjectRepositoryはProjectRepositoryインターフェースを実装している', function () {
    $repository = new EloquentProjectRepository;
    expect($repository)->toBeInstanceOf(ProjectRepository::class);
});

describe('findAll()メソッド', function () {
    test('全プロジェクトを取得できる', function () {
        $repository = new EloquentProjectRepository;

        // テストデータを作成
        $project1 = createProject(1, 'group/project1', 'Description 1', 'main');
        $project2 = createProject(2, 'group/project2', 'Description 2', 'develop');

        $repository->save($project1);
        $repository->save($project2);

        $result = $repository->findAll();

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result)->toHaveCount(2);
        expect($result->pluck('id.value')->toArray())->toContain(1, 2);
    });

    test('プロジェクトが存在しない場合は空のCollectionを返す', function () {
        $repository = new EloquentProjectRepository;

        $result = $repository->findAll();

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result)->toHaveCount(0);
    });
});

describe('findByProjectId()メソッド', function () {
    test('プロジェクトIDでプロジェクトを取得できる', function () {
        $repository = new EloquentProjectRepository;

        $project = createProject(1, 'group/project', 'Description', 'main');

        $repository->save($project);

        $result = $repository->findByProjectId(new ProjectId(1));

        expect($result)->toBeInstanceOf(Project::class);
        expect($result->id->value)->toBe(1);
        expect($result->nameWithNamespace->value)->toBe('group/project');
    });

    test('存在しないプロジェクトIDの場合はnullを返す', function () {
        $repository = new EloquentProjectRepository;

        $result = $repository->findByProjectId(new ProjectId(999));

        expect($result)->toBeNull();
    });
});

describe('save()メソッド', function () {
    test('新規プロジェクトを保存できる', function () {
        $repository = new EloquentProjectRepository;

        $project = createProject(1, 'group/project', 'Description', 'main');

        $result = $repository->save($project);

        expect($result)->toBeInstanceOf(Project::class);
        expect($result->id->value)->toBe(1);

        // データベースから取得して確認
        $saved = $repository->findByProjectId(new ProjectId(1));
        expect($saved)->not->toBeNull();
        expect($saved->nameWithNamespace->value)->toBe('group/project');
    });

    test('既存プロジェクトを更新できる', function () {
        $repository = new EloquentProjectRepository;

        $project1 = createProject(1, 'group/project', 'Old Description', 'main');
        $repository->save($project1);

        $project2 = createProject(1, 'group/project-updated', 'New Description', 'develop');

        $result = $repository->save($project2);

        expect($result->nameWithNamespace->value)->toBe('group/project-updated');
        expect($result->description->value)->toBe('New Description');
        expect($result->defaultBranch->value)->toBe('develop');

        // データベースから取得して確認
        $saved = $repository->findByProjectId(new ProjectId(1));
        expect($saved->nameWithNamespace->value)->toBe('group/project-updated');
    });

    test('nullのdescriptionとdefaultBranchを保存できる', function () {
        $repository = new EloquentProjectRepository;

        $project = createProject(1, 'group/project');

        $result = $repository->save($project);

        expect($result->description->value)->toBeNull();
        expect($result->defaultBranch->value)->toBeNull();

        $saved = $repository->findByProjectId(new ProjectId(1));
        expect($saved->description->value)->toBeNull();
        expect($saved->defaultBranch->value)->toBeNull();
    });
});

describe('saveMany()メソッド', function () {
    test('複数のプロジェクトを一括保存できる', function () {
        $repository = new EloquentProjectRepository;

        $projects = collect([
            createProject(1, 'group/project1'),
            createProject(2, 'group/project2'),
            createProject(3, 'group/project3'),
        ]);

        $repository->saveMany($projects);

        $all = $repository->findAll();
        expect($all)->toHaveCount(3);
    });

    test('既存プロジェクトと新規プロジェクトを混在して保存できる', function () {
        $repository = new EloquentProjectRepository;

        // 既存プロジェクトを作成
        $existing = createProject(1, 'group/project1');
        $repository->save($existing);

        // 既存と新規を混在
        $projects = collect([
            createProject(1, 'group/project1-updated'),
            createProject(2, 'group/project2'),
        ]);

        $repository->saveMany($projects);

        $all = $repository->findAll();
        expect($all)->toHaveCount(2);
        expect($all->first(fn ($p) => $p->id->value === 1)->nameWithNamespace->value)
            ->toBe('group/project1-updated');
    });
});

describe('delete()メソッド', function () {
    test('プロジェクトを削除できる', function () {
        $repository = new EloquentProjectRepository;

        $project = createProject(1, 'group/project');
        $repository->save($project);

        $repository->delete($project);

        $result = $repository->findByProjectId(new ProjectId(1));
        expect($result)->toBeNull();
    });

    test('存在しないプロジェクトを削除してもエラーにならない', function () {
        $repository = new EloquentProjectRepository;

        $project = createProject(999, 'group/nonexistent');

        // 存在しないプロジェクトを削除しても例外がスローされないことを確認
        $repository->delete($project);

        expect(true)->toBeTrue(); // 例外がスローされなければ成功
    });
});

describe('findNotInProjectIds()メソッド', function () {
    test('指定されたプロジェクトIDリストに存在しないプロジェクトを取得できる', function () {
        $repository = new EloquentProjectRepository;

        // テストデータを作成
        $repository->save(createProject(1, 'group/project1'));
        $repository->save(createProject(2, 'group/project2'));
        $repository->save(createProject(3, 'group/project3'));

        $result = $repository->findNotInProjectIds(collect([
            new ProjectId(1),
            new ProjectId(2),
        ]));

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result)->toHaveCount(1);
        expect($result->first()->id->value)->toBe(3);
    });

    test('すべてのプロジェクトIDが指定された場合は空のCollectionを返す', function () {
        $repository = new EloquentProjectRepository;

        $repository->save(createProject(1, 'group/project1'));

        $result = $repository->findNotInProjectIds(collect([
            new ProjectId(1),
        ]));

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result)->toHaveCount(0);
    });

    test('空のプロジェクトIDリストを指定した場合は全プロジェクトを返す', function () {
        $repository = new EloquentProjectRepository;

        $repository->save(createProject(1, 'group/project1'));
        $repository->save(createProject(2, 'group/project2'));

        $result = $repository->findNotInProjectIds(collect([]));

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result)->toHaveCount(2);
    });
});
