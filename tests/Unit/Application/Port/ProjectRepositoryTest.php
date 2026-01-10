<?php

use App\Application\Port\ProjectRepository;
use App\Domain\Project;
use App\Domain\ValueObjects\ProjectId;
use Illuminate\Support\Collection;

// ヘルパー関数
function assertMethodExists(ReflectionClass $reflection, string $methodName): void
{
    expect($reflection->hasMethod($methodName))->toBeTrue();
}

function assertMethodSignature(
    ReflectionClass $reflection,
    string $methodName,
    int $parameterCount,
    ?string $firstParameterType = null,
    ?string $returnType = null,
    bool $returnTypeAllowsNull = false
): void {
    $method = $reflection->getMethod($methodName);
    expect($method->isPublic())->toBeTrue();

    $parameters = $method->getParameters();
    expect($parameters)->toHaveCount($parameterCount);

    if ($firstParameterType !== null && count($parameters) > 0) {
        expect($parameters[0]->getType()?->getName())->toBe($firstParameterType);
    }

    if ($returnType !== null) {
        $returnTypeReflection = $method->getReturnType();
        expect($returnTypeReflection)->not->toBeNull();
        expect($returnTypeReflection->getName())->toBe($returnType);
        expect($returnTypeReflection->allowsNull())->toBe($returnTypeAllowsNull);
    }
}

test('ProjectRepositoryインターフェースが存在する', function () {
    expect(interface_exists(ProjectRepository::class))->toBeTrue();
});

describe('ProjectRepositoryインターフェースのメソッド定義', function () {
    $reflection = new ReflectionClass(ProjectRepository::class);

    test('findAll()メソッドが定義されている', function () use ($reflection) {
        assertMethodSignature($reflection, 'findAll', 0, null, Collection::class);
    });

    test('findByProjectId()メソッドが定義されている', function () use ($reflection) {
        assertMethodSignature($reflection, 'findByProjectId', 1, ProjectId::class, Project::class, true);
    });

    test('save()メソッドが定義されている', function () use ($reflection) {
        assertMethodSignature($reflection, 'save', 1, Project::class, Project::class);
    });

    test('saveMany()メソッドが定義されている', function () use ($reflection) {
        assertMethodSignature($reflection, 'saveMany', 1, Collection::class, 'void');
    });

    test('delete()メソッドが定義されている', function () use ($reflection) {
        assertMethodSignature($reflection, 'delete', 1, Project::class, 'void');
    });

    test('findNotInProjectIds()メソッドが定義されている', function () use ($reflection) {
        assertMethodSignature($reflection, 'findNotInProjectIds', 1, Collection::class, Collection::class);
    });
});
