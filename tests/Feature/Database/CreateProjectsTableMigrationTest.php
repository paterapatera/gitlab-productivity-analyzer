<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('projectsテーブルが存在する', function () {
    expect(Schema::hasTable('projects'))->toBeTrue();
});

test('projectsテーブルにidカラムが存在する', function () {
    expect(Schema::hasColumn('projects', 'id'))->toBeTrue();
});

test('projectsテーブルのidカラムはBIGINT型である', function () {
    $column = Schema::getColumnType('projects', 'id');

    // PostgreSQLでは'int8'、MySQLでは'bigint'として返される
    expect(in_array($column, ['bigint', 'int8']))->toBeTrue();
});

test('projectsテーブルのidカラムはプライマリキーである', function () {
    $constraints = DB::select("
        SELECT constraint_name, constraint_type
        FROM information_schema.table_constraints
        WHERE table_name = 'projects' AND constraint_type = 'PRIMARY KEY'
    ");

    expect(count($constraints))->toBeGreaterThan(0);
});

test('projectsテーブルにdescriptionカラムが存在する', function () {
    expect(Schema::hasColumn('projects', 'description'))->toBeTrue();
});

test('projectsテーブルのdescriptionカラムはTEXT型でnullableである', function () {
    $column = Schema::getColumnType('projects', 'description');
    $columnInfo = DB::selectOne("
        SELECT is_nullable
        FROM information_schema.columns
        WHERE table_name = 'projects' AND column_name = 'description'
    ");

    expect($column)->toBe('text');
    expect($columnInfo->is_nullable)->toBe('YES');
});

test('projectsテーブルにname_with_namespaceカラムが存在する', function () {
    expect(Schema::hasColumn('projects', 'name_with_namespace'))->toBeTrue();
});

test('projectsテーブルのname_with_namespaceカラムはVARCHAR(500)型でNOT NULLである', function () {
    $column = Schema::getColumnType('projects', 'name_with_namespace');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'projects' AND column_name = 'name_with_namespace'
    ");

    // PostgreSQLでは'varchar'、MySQLでは'string'として返される
    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(500);
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('projectsテーブルにdefault_branchカラムが存在する', function () {
    expect(Schema::hasColumn('projects', 'default_branch'))->toBeTrue();
});

test('projectsテーブルのdefault_branchカラムはVARCHAR(255)型でnullableである', function () {
    $column = Schema::getColumnType('projects', 'default_branch');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'projects' AND column_name = 'default_branch'
    ");

    // PostgreSQLでは'varchar'、MySQLでは'string'として返される
    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(255);
    expect($columnInfo->is_nullable)->toBe('YES');
});

test('projectsテーブルにdeleted_atカラムが存在する', function () {
    expect(Schema::hasColumn('projects', 'deleted_at'))->toBeTrue();
});

test('projectsテーブルのdeleted_atカラムはTIMESTAMP型でnullableである', function () {
    $column = Schema::getColumnType('projects', 'deleted_at');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'projects' AND column_name = 'deleted_at'
    ");

    // PostgreSQLでは'timestamp'、MySQLでは'datetime'として返される
    expect(in_array($column, ['datetime', 'timestamp']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('YES');
});

test('projectsテーブルのdeleted_atカラムにインデックスが存在する', function () {
    $indexes = DB::select("
        SELECT indexname, indexdef
        FROM pg_indexes
        WHERE tablename = 'projects' AND indexdef LIKE '%deleted_at%'
    ");

    expect(count($indexes))->toBeGreaterThan(0);
});
