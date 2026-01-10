<?php

use App\Domain\Project;
use App\Domain\ValueObjects\DefaultBranch;
use App\Domain\ValueObjects\ProjectDescription;
use App\Domain\ValueObjects\ProjectId;
use App\Domain\ValueObjects\ProjectNameWithNamespace;

test('必須フィールドでProjectエンティティを作成できる', function () {
    $projectId = new ProjectId(123);
    $nameWithNamespace = new ProjectNameWithNamespace('group/project');

    $project = new Project(
        id: $projectId,
        nameWithNamespace: $nameWithNamespace
    );

    expect($project->id)->toBe($projectId);
    expect($project->nameWithNamespace)->toBe($nameWithNamespace);
    expect($project->description)->toBeInstanceOf(ProjectDescription::class);
    expect($project->defaultBranch)->toBeInstanceOf(DefaultBranch::class);
    expect($project->description->value)->toBeNull();
    expect($project->defaultBranch->value)->toBeNull();
});

test('すべてのフィールドでProjectエンティティを作成できる', function () {
    $projectId = new ProjectId(123);
    $nameWithNamespace = new ProjectNameWithNamespace('group/project');
    $description = new ProjectDescription('Project description');
    $defaultBranch = new DefaultBranch('main');

    $project = new Project(
        id: $projectId,
        nameWithNamespace: $nameWithNamespace,
        description: $description,
        defaultBranch: $defaultBranch
    );

    expect($project->id)->toBe($projectId);
    expect($project->nameWithNamespace)->toBe($nameWithNamespace);
    expect($project->description)->toBe($description);
    expect($project->defaultBranch)->toBe($defaultBranch);
});

test('Projectエンティティは不変である', function () {
    $project = createProject();

    expect($project)->toHaveProperty('id');
    expect($project)->toHaveProperty('nameWithNamespace');
    expect($project)->toHaveProperty('description');
    expect($project)->toHaveProperty('defaultBranch');
    expect($project->description)->toBeInstanceOf(ProjectDescription::class);
    expect($project->defaultBranch)->toBeInstanceOf(DefaultBranch::class);
});

test('Projectエンティティは必須フィールドを検証する', function () {
    expect(fn () => new Project(
        id: null,
        nameWithNamespace: new ProjectNameWithNamespace('group/project')
    ))->toThrow(TypeError::class);

    expect(fn () => new Project(
        id: new ProjectId(123),
        nameWithNamespace: null
    ))->toThrow(TypeError::class);
});

describe('等価性の比較', function () {
    test('同じ値のProjectエンティティは等価である', function () {
        $project1 = createProject(123, 'group/project', 'Description', 'main');
        $project2 = createProject(123, 'group/project', 'Description', 'main');

        expect($project1->equals($project2))->toBeTrue();
    });

    test('descriptionとdefaultBranchを省略した場合と明示的にnullを渡した場合のProjectエンティティは等価である', function () {
        $project1 = new Project(
            id: new ProjectId(123),
            nameWithNamespace: new ProjectNameWithNamespace('group/project')
        );

        $project2 = new Project(
            id: new ProjectId(123),
            nameWithNamespace: new ProjectNameWithNamespace('group/project'),
            description: new ProjectDescription(null),
            defaultBranch: new DefaultBranch(null)
        );

        expect($project1->equals($project2))->toBeTrue();
    });

    test('異なるidのProjectエンティティは等価でない', function () {
        $project1 = createProject(123, 'group/project');
        $project2 = createProject(456, 'group/project');

        expect($project1->equals($project2))->toBeFalse();
    });

    test('異なるnameWithNamespaceのProjectエンティティは等価でない', function () {
        $project1 = createProject(123, 'group/project1');
        $project2 = createProject(123, 'group/project2');

        expect($project1->equals($project2))->toBeFalse();
    });

    test('異なるdescriptionのProjectエンティティは等価でない', function () {
        $project1 = createProject(123, 'group/project', 'Description 1');
        $project2 = createProject(123, 'group/project', 'Description 2');

        expect($project1->equals($project2))->toBeFalse();
    });

    test('異なるdefaultBranchのProjectエンティティは等価でない', function () {
        $project1 = createProject(123, 'group/project', null, 'main');
        $project2 = createProject(123, 'group/project', null, 'develop');

        expect($project1->equals($project2))->toBeFalse();
    });

    test('descriptionがnullと値がある場合のProjectエンティティは等価でない', function () {
        $project1 = createProject(123, 'group/project', null);
        $project2 = createProject(123, 'group/project', 'Description');

        expect($project1->equals($project2))->toBeFalse();
    });

    test('defaultBranchがnullと値がある場合のProjectエンティティは等価でない', function () {
        $project1 = createProject(123, 'group/project', null, null);
        $project2 = createProject(123, 'group/project', null, 'main');

        expect($project1->equals($project2))->toBeFalse();
    });
});
