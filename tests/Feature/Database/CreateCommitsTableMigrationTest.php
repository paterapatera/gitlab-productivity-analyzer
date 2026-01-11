<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('commitsテーブルが存在する', function () {
    expect(Schema::hasTable('commits'))->toBeTrue();
});

test('commitsテーブルに複合プライマリキー（project_id, branch_name, sha）が存在する', function () {
    $constraints = DB::select("
        SELECT constraint_name, constraint_type
        FROM information_schema.table_constraints
        WHERE table_name = 'commits' AND constraint_type = 'PRIMARY KEY'
    ");

    expect(count($constraints))->toBeGreaterThan(0);

    // 複合プライマリキーのカラムを確認
    $primaryKeyColumns = DB::select("
        SELECT column_name
        FROM information_schema.key_column_usage
        WHERE table_name = 'commits' AND constraint_name IN (
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_name = 'commits' AND constraint_type = 'PRIMARY KEY'
        )
        ORDER BY ordinal_position
    ");

    $columnNames = array_map(fn ($col) => $col->column_name, $primaryKeyColumns);
    expect($columnNames)->toContain('project_id', 'branch_name', 'sha');
});

test('commitsテーブルにproject_idカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'project_id'))->toBeTrue();
});

test('commitsテーブルのproject_idカラムはBIGINT型でNOT NULLである', function () {
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'project_id'
    ");

    // PostgreSQLでは'bigint'、MySQLでは'bigint'として返される
    expect(in_array($columnInfo->data_type, ['bigint', 'int8']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commitsテーブルにbranch_nameカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'branch_name'))->toBeTrue();
});

test('commitsテーブルのbranch_nameカラムはVARCHAR(255)型でNOT NULLである', function () {
    $column = Schema::getColumnType('commits', 'branch_name');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'branch_name'
    ");

    // PostgreSQLでは'varchar'、MySQLでは'string'として返される
    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(255);
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commitsテーブルにshaカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'sha'))->toBeTrue();
});

test('commitsテーブルのshaカラムはVARCHAR(40)型でNOT NULLである', function () {
    $column = Schema::getColumnType('commits', 'sha');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'sha'
    ");

    // PostgreSQLでは'varchar'、MySQLでは'string'として返される
    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(40);
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commitsテーブルにmessageカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'message'))->toBeTrue();
});

test('commitsテーブルのmessageカラムはTEXT型でnullableである', function () {
    $column = Schema::getColumnType('commits', 'message');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'message'
    ");

    // PostgreSQLでは'text'、MySQLでは'text'として返される
    expect(in_array($column, ['text']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('YES');
});

test('commitsテーブルにcommitted_dateカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'committed_date'))->toBeTrue();
});

test('commitsテーブルのcommitted_dateカラムはTIMESTAMP型でNOT NULLである', function () {
    $column = Schema::getColumnType('commits', 'committed_date');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'committed_date'
    ");

    // PostgreSQLでは'timestamp'、MySQLでは'timestamp'として返される
    expect(in_array($column, ['timestamp', 'datetime']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commitsテーブルにauthor_nameカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'author_name'))->toBeTrue();
});

test('commitsテーブルのauthor_nameカラムはVARCHAR(255)型でnullableである', function () {
    $column = Schema::getColumnType('commits', 'author_name');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'author_name'
    ");

    // PostgreSQLでは'varchar'、MySQLでは'string'として返される
    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(255);
    expect($columnInfo->is_nullable)->toBe('YES');
});

test('commitsテーブルにauthor_emailカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'author_email'))->toBeTrue();
});

test('commitsテーブルのauthor_emailカラムはVARCHAR(255)型でnullableである', function () {
    $column = Schema::getColumnType('commits', 'author_email');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'author_email'
    ");

    // PostgreSQLでは'varchar'、MySQLでは'string'として返される
    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(255);
    expect($columnInfo->is_nullable)->toBe('YES');
});

test('commitsテーブルにadditionsカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'additions'))->toBeTrue();
});

test('commitsテーブルのadditionsカラムはINTEGER型でNOT NULLでデフォルト値0である', function () {
    $column = Schema::getColumnType('commits', 'additions');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'additions'
    ");

    // PostgreSQLでは'int4'、MySQLでは'int'として返される
    expect(in_array($column, ['integer', 'int', 'int4']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
    expect($columnInfo->column_default)->toBe('0');
});

test('commitsテーブルにdeletionsカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'deletions'))->toBeTrue();
});

test('commitsテーブルのdeletionsカラムはINTEGER型でNOT NULLでデフォルト値0である', function () {
    $column = Schema::getColumnType('commits', 'deletions');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'deletions'
    ");

    // PostgreSQLでは'int4'、MySQLでは'int'として返される
    expect(in_array($column, ['integer', 'int', 'int4']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
    expect($columnInfo->column_default)->toBe('0');
});

test('commitsテーブルにcreated_atカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'created_at'))->toBeTrue();
});

test('commitsテーブルのcreated_atカラムはTIMESTAMP型でnullableである', function () {
    $column = Schema::getColumnType('commits', 'created_at');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'created_at'
    ");

    // PostgreSQLでは'timestamp'、MySQLでは'timestamp'として返される
    expect(in_array($column, ['timestamp', 'datetime']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('YES');
});

test('commitsテーブルにupdated_atカラムが存在する', function () {
    expect(Schema::hasColumn('commits', 'updated_at'))->toBeTrue();
});

test('commitsテーブルのupdated_atカラムはTIMESTAMP型でnullableである', function () {
    $column = Schema::getColumnType('commits', 'updated_at');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commits' AND column_name = 'updated_at'
    ");

    // PostgreSQLでは'timestamp'、MySQLでは'timestamp'として返される
    expect(in_array($column, ['timestamp', 'datetime']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('YES');
});

test('commitsテーブルにproject_idのインデックスが存在する', function () {
    $indexes = DB::select("
        SELECT indexname
        FROM pg_indexes
        WHERE tablename = 'commits' AND indexname LIKE '%project_id%'
    ");

    // PostgreSQLの場合
    if (count($indexes) > 0) {
        expect(count($indexes))->toBeGreaterThan(0);
    } else {
        // MySQLの場合、別の方法で確認
        $indexes = DB::select("
            SHOW INDEX FROM commits WHERE Column_name = 'project_id'
        ");
        expect(count($indexes))->toBeGreaterThan(0);
    }
});

test('commitsテーブルにcommitted_dateのインデックスが存在する', function () {
    $indexes = DB::select("
        SELECT indexname
        FROM pg_indexes
        WHERE tablename = 'commits' AND indexname LIKE '%committed_date%'
    ");

    // PostgreSQLの場合
    if (count($indexes) > 0) {
        expect(count($indexes))->toBeGreaterThan(0);
    } else {
        // MySQLの場合、別の方法で確認
        $indexes = DB::select("
            SHOW INDEX FROM commits WHERE Column_name = 'committed_date'
        ");
        expect(count($indexes))->toBeGreaterThan(0);
    }
});
