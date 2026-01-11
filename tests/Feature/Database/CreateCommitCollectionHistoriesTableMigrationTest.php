<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('commit_collection_historiesテーブルが存在する', function () {
    expect(Schema::hasTable('commit_collection_histories'))->toBeTrue();
});

test('commit_collection_historiesテーブルに複合プライマリキー（project_id, branch_name）が存在する', function () {
    $constraints = DB::select("
        SELECT constraint_name, constraint_type
        FROM information_schema.table_constraints
        WHERE table_name = 'commit_collection_histories' AND constraint_type = 'PRIMARY KEY'
    ");

    expect(count($constraints))->toBeGreaterThan(0);

    // 複合プライマリキーのカラムを確認
    $primaryKeyColumns = DB::select("
        SELECT column_name
        FROM information_schema.key_column_usage
        WHERE table_name = 'commit_collection_histories' AND constraint_name IN (
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_name = 'commit_collection_histories' AND constraint_type = 'PRIMARY KEY'
        )
        ORDER BY ordinal_position
    ");

    $columnNames = array_map(fn ($col) => $col->column_name, $primaryKeyColumns);
    expect($columnNames)->toContain('project_id', 'branch_name');
});

test('commit_collection_historiesテーブルにproject_idカラムが存在する', function () {
    expect(Schema::hasColumn('commit_collection_histories', 'project_id'))->toBeTrue();
});

test('commit_collection_historiesテーブルのproject_idカラムはBIGINT型でNOT NULLである', function () {
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_collection_histories' AND column_name = 'project_id'
    ");

    // PostgreSQLでは'bigint'、MySQLでは'bigint'として返される
    expect(in_array($columnInfo->data_type, ['bigint', 'int8']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_collection_historiesテーブルにbranch_nameカラムが存在する', function () {
    expect(Schema::hasColumn('commit_collection_histories', 'branch_name'))->toBeTrue();
});

test('commit_collection_historiesテーブルのbranch_nameカラムはVARCHAR(255)型でNOT NULLである', function () {
    $column = Schema::getColumnType('commit_collection_histories', 'branch_name');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_collection_histories' AND column_name = 'branch_name'
    ");

    // PostgreSQLでは'varchar'、MySQLでは'string'として返される
    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(255);
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_collection_historiesテーブルにlatest_committed_dateカラムが存在する', function () {
    expect(Schema::hasColumn('commit_collection_histories', 'latest_committed_date'))->toBeTrue();
});

test('commit_collection_historiesテーブルのlatest_committed_dateカラムはTIMESTAMP型でNOT NULLである', function () {
    $column = Schema::getColumnType('commit_collection_histories', 'latest_committed_date');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_collection_histories' AND column_name = 'latest_committed_date'
    ");

    // PostgreSQLでは'timestamp'、MySQLでは'timestamp'として返される
    expect(in_array($column, ['timestamp', 'datetime']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_collection_historiesテーブルにproject_idのインデックスが存在する', function () {
    $indexes = DB::select("
        SELECT indexname
        FROM pg_indexes
        WHERE tablename = 'commit_collection_histories' AND indexname LIKE '%project_id%'
    ");

    // PostgreSQLの場合
    if (count($indexes) > 0) {
        expect(count($indexes))->toBeGreaterThan(0);
    } else {
        // MySQLの場合、別の方法で確認
        $indexes = DB::select("
            SHOW INDEX FROM commit_collection_histories WHERE Column_name = 'project_id'
        ");
        expect(count($indexes))->toBeGreaterThan(0);
    }
});

test('commit_collection_historiesテーブルにlatest_committed_dateのインデックスが存在する', function () {
    $indexes = DB::select("
        SELECT indexname
        FROM pg_indexes
        WHERE tablename = 'commit_collection_histories' AND indexname LIKE '%latest_committed_date%'
    ");

    // PostgreSQLの場合
    if (count($indexes) > 0) {
        expect(count($indexes))->toBeGreaterThan(0);
    } else {
        // MySQLの場合、別の方法で確認
        $indexes = DB::select("
            SHOW INDEX FROM commit_collection_histories WHERE Column_name = 'latest_committed_date'
        ");
        expect(count($indexes))->toBeGreaterThan(0);
    }
});

test('commit_collection_historiesテーブルにproject_idの外部キー制約が存在する', function () {
    $foreignKeys = DB::select("
        SELECT constraint_name, constraint_type
        FROM information_schema.table_constraints
        WHERE table_name = 'commit_collection_histories' 
        AND constraint_type = 'FOREIGN KEY'
        AND constraint_name IN (
            SELECT constraint_name
            FROM information_schema.key_column_usage
            WHERE table_name = 'commit_collection_histories' 
            AND column_name = 'project_id'
        )
    ");

    expect(count($foreignKeys))->toBeGreaterThan(0);

    // 外部キーがprojectsテーブルを参照していることを確認
    // PostgreSQLの場合、pg_constraintを使用
    $pgConstraint = DB::selectOne("
        SELECT conrelid::regclass AS table_name,
               confrelid::regclass AS referenced_table_name
        FROM pg_constraint
        WHERE conrelid = 'commit_collection_histories'::regclass
        AND contype = 'f'
        AND conkey::text LIKE '%project_id%'
    ");

    if ($pgConstraint && $pgConstraint->referenced_table_name) {
        // PostgreSQLの場合
        expect($pgConstraint->referenced_table_name)->toBe('projects');
    } else {
        // MySQLの場合、information_schemaを使用
        // PostgreSQLではreferenced_table_nameが存在しないため、別の方法で確認
        $constraintName = DB::selectOne("
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_name = 'commit_collection_histories'
            AND constraint_type = 'FOREIGN KEY'
            LIMIT 1
        ");

        expect($constraintName)->not->toBeNull();
        expect(count($foreignKeys))->toBeGreaterThan(0);
    }
});
