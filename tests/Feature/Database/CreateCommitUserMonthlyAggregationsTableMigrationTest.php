<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('commit_user_monthly_aggregationsテーブルが存在する', function () {
    expect(Schema::hasTable('commit_user_monthly_aggregations'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルに複合プライマリキー（project_id, branch_name, author_email, year, month）が存在する', function () {
    $constraints = DB::select("
        SELECT constraint_name, constraint_type
        FROM information_schema.table_constraints
        WHERE table_name = 'commit_user_monthly_aggregations' AND constraint_type = 'PRIMARY KEY'
    ");

    expect(count($constraints))->toBeGreaterThan(0);

    // 複合プライマリキーのカラムを確認
    $primaryKeyColumns = DB::select("
        SELECT column_name
        FROM information_schema.key_column_usage
        WHERE table_name = 'commit_user_monthly_aggregations' AND constraint_name IN (
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_name = 'commit_user_monthly_aggregations' AND constraint_type = 'PRIMARY KEY'
        )
        ORDER BY ordinal_position
    ");

    $columnNames = array_map(fn ($col) => $col->column_name, $primaryKeyColumns);
    expect($columnNames)->toContain('project_id', 'branch_name', 'author_email', 'year', 'month');
});

test('commit_user_monthly_aggregationsテーブルにproject_idカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'project_id'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのproject_idカラムはBIGINT型でNOT NULLである', function () {
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'project_id'
    ");

    expect(in_array($columnInfo->data_type, ['bigint', 'int8']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_user_monthly_aggregationsテーブルにbranch_nameカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'branch_name'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのbranch_nameカラムはVARCHAR(255)型でNOT NULLである', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'branch_name');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'branch_name'
    ");

    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(255);
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_user_monthly_aggregationsテーブルにauthor_emailカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'author_email'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのauthor_emailカラムはVARCHAR(255)型でNOT NULLである', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'author_email');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'author_email'
    ");

    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(255);
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_user_monthly_aggregationsテーブルにyearカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'year'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのyearカラムはINTEGER型でNOT NULLである', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'year');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'year'
    ");

    expect(in_array($column, ['integer', 'int', 'int4']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_user_monthly_aggregationsテーブルのyearカラムにCHECK制約（1-9999）が存在する', function () {
    $constraints = DB::select("
        SELECT constraint_name, check_clause
        FROM information_schema.check_constraints
        WHERE constraint_name IN (
            SELECT constraint_name
            FROM information_schema.constraint_column_usage
            WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'year'
        )
    ");

    $hasYearCheck = false;
    foreach ($constraints as $constraint) {
        if (str_contains($constraint->check_clause, 'year') &&
            str_contains($constraint->check_clause, '1') &&
            str_contains($constraint->check_clause, '9999')) {
            $hasYearCheck = true;
            break;
        }
    }

    expect($hasYearCheck)->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルにmonthカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'month'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのmonthカラムはINTEGER型でNOT NULLである', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'month');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'month'
    ");

    expect(in_array($column, ['integer', 'int', 'int4']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_user_monthly_aggregationsテーブルのmonthカラムにCHECK制約（1-12）が存在する', function () {
    $constraints = DB::select("
        SELECT constraint_name, check_clause
        FROM information_schema.check_constraints
        WHERE constraint_name IN (
            SELECT constraint_name
            FROM information_schema.constraint_column_usage
            WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'month'
        )
    ");

    $hasMonthCheck = false;
    foreach ($constraints as $constraint) {
        if (str_contains($constraint->check_clause, 'month') &&
            str_contains($constraint->check_clause, '1') &&
            str_contains($constraint->check_clause, '12')) {
            $hasMonthCheck = true;
            break;
        }
    }

    expect($hasMonthCheck)->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルにauthor_nameカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'author_name'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのauthor_nameカラムはVARCHAR(255)型でnullableである', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'author_name');
    $columnInfo = DB::selectOne("
        SELECT character_maximum_length, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'author_name'
    ");

    expect(in_array($column, ['string', 'varchar']))->toBeTrue();
    expect((int) $columnInfo->character_maximum_length)->toBe(255);
    expect($columnInfo->is_nullable)->toBe('YES');
});

test('commit_user_monthly_aggregationsテーブルにtotal_additionsカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'total_additions'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのtotal_additionsカラムはINTEGER型でNOT NULLでデフォルト値0である', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'total_additions');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'total_additions'
    ");

    expect(in_array($column, ['integer', 'int', 'int4']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
    expect($columnInfo->column_default)->toBe('0');
});

test('commit_user_monthly_aggregationsテーブルのtotal_additionsカラムにCHECK制約（>= 0）が存在する', function () {
    $constraints = DB::select("
        SELECT constraint_name, check_clause
        FROM information_schema.check_constraints
        WHERE constraint_name IN (
            SELECT constraint_name
            FROM information_schema.constraint_column_usage
            WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'total_additions'
        )
    ");

    $hasAdditionsCheck = false;
    foreach ($constraints as $constraint) {
        if (str_contains($constraint->check_clause, 'total_additions') &&
            str_contains($constraint->check_clause, '0')) {
            $hasAdditionsCheck = true;
            break;
        }
    }

    expect($hasAdditionsCheck)->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルにtotal_deletionsカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'total_deletions'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのtotal_deletionsカラムはINTEGER型でNOT NULLでデフォルト値0である', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'total_deletions');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'total_deletions'
    ");

    expect(in_array($column, ['integer', 'int', 'int4']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
    expect($columnInfo->column_default)->toBe('0');
});

test('commit_user_monthly_aggregationsテーブルのtotal_deletionsカラムにCHECK制約（>= 0）が存在する', function () {
    $constraints = DB::select("
        SELECT constraint_name, check_clause
        FROM information_schema.check_constraints
        WHERE constraint_name IN (
            SELECT constraint_name
            FROM information_schema.constraint_column_usage
            WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'total_deletions'
        )
    ");

    $hasDeletionsCheck = false;
    foreach ($constraints as $constraint) {
        if (str_contains($constraint->check_clause, 'total_deletions') &&
            str_contains($constraint->check_clause, '0')) {
            $hasDeletionsCheck = true;
            break;
        }
    }

    expect($hasDeletionsCheck)->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルにcommit_countカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'commit_count'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのcommit_countカラムはINTEGER型でNOT NULLでデフォルト値0である', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'commit_count');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'commit_count'
    ");

    expect(in_array($column, ['integer', 'int', 'int4']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
    expect($columnInfo->column_default)->toBe('0');
});

test('commit_user_monthly_aggregationsテーブルのcommit_countカラムにCHECK制約（>= 0）が存在する', function () {
    $constraints = DB::select("
        SELECT constraint_name, check_clause
        FROM information_schema.check_constraints
        WHERE constraint_name IN (
            SELECT constraint_name
            FROM information_schema.constraint_column_usage
            WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'commit_count'
        )
    ");

    $hasCommitCountCheck = false;
    foreach ($constraints as $constraint) {
        if (str_contains($constraint->check_clause, 'commit_count') &&
            str_contains($constraint->check_clause, '0')) {
            $hasCommitCountCheck = true;
            break;
        }
    }

    expect($hasCommitCountCheck)->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルにcreated_atカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'created_at'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのcreated_atカラムはTIMESTAMP型でNOT NULLである', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'created_at');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'created_at'
    ");

    expect(in_array($column, ['timestamp', 'datetime']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_user_monthly_aggregationsテーブルにupdated_atカラムが存在する', function () {
    expect(Schema::hasColumn('commit_user_monthly_aggregations', 'updated_at'))->toBeTrue();
});

test('commit_user_monthly_aggregationsテーブルのupdated_atカラムはTIMESTAMP型でNOT NULLである', function () {
    $column = Schema::getColumnType('commit_user_monthly_aggregations', 'updated_at');
    $columnInfo = DB::selectOne("
        SELECT data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'commit_user_monthly_aggregations' AND column_name = 'updated_at'
    ");

    expect(in_array($column, ['timestamp', 'datetime']))->toBeTrue();
    expect($columnInfo->is_nullable)->toBe('NO');
});

test('commit_user_monthly_aggregationsテーブルにproject_idの外部キー制約が存在する', function () {
    $foreignKeys = DB::select("
        SELECT k.constraint_name, k.table_name, k.column_name, f.table_name AS foreign_table_name, f.column_name AS foreign_column_name
        FROM information_schema.key_column_usage k
        JOIN information_schema.referential_constraints r ON k.constraint_name = r.constraint_name
        JOIN information_schema.key_column_usage f ON r.unique_constraint_name = f.constraint_name AND f.ordinal_position = k.position_in_unique_constraint
        WHERE k.table_name = 'commit_user_monthly_aggregations' 
        AND k.column_name = 'project_id'
    ");

    expect(count($foreignKeys))->toBeGreaterThan(0);
    expect($foreignKeys[0]->foreign_table_name)->toBe('projects');
    expect($foreignKeys[0]->foreign_column_name)->toBe('id');
});

test('commit_user_monthly_aggregationsテーブルにidx_project_branchインデックスが存在する', function () {
    $indexes = DB::select("
        SELECT indexname
        FROM pg_indexes
        WHERE tablename = 'commit_user_monthly_aggregations' AND indexname = 'idx_project_branch'
    ");

    // PostgreSQLの場合
    if (count($indexes) > 0) {
        expect(count($indexes))->toBeGreaterThan(0);
    } else {
        // MySQLの場合、別の方法で確認
        $indexes = DB::select("
            SHOW INDEX FROM commit_user_monthly_aggregations WHERE Key_name = 'idx_project_branch'
        ");
        expect(count($indexes))->toBeGreaterThan(0);
    }
});

test('commit_user_monthly_aggregationsテーブルにidx_year_monthインデックスが存在する', function () {
    $indexes = DB::select("
        SELECT indexname
        FROM pg_indexes
        WHERE tablename = 'commit_user_monthly_aggregations' AND indexname = 'idx_year_month'
    ");

    // PostgreSQLの場合
    if (count($indexes) > 0) {
        expect(count($indexes))->toBeGreaterThan(0);
    } else {
        // MySQLの場合、別の方法で確認
        $indexes = DB::select("
            SHOW INDEX FROM commit_user_monthly_aggregations WHERE Key_name = 'idx_year_month'
        ");
        expect(count($indexes))->toBeGreaterThan(0);
    }
});

test('commit_user_monthly_aggregationsテーブルにidx_author_emailインデックスが存在する', function () {
    $indexes = DB::select("
        SELECT indexname
        FROM pg_indexes
        WHERE tablename = 'commit_user_monthly_aggregations' AND indexname = 'idx_author_email'
    ");

    // PostgreSQLの場合
    if (count($indexes) > 0) {
        expect(count($indexes))->toBeGreaterThan(0);
    } else {
        // MySQLの場合、別の方法で確認
        $indexes = DB::select("
            SHOW INDEX FROM commit_user_monthly_aggregations WHERE Key_name = 'idx_author_email'
        ");
        expect(count($indexes))->toBeGreaterThan(0);
    }
});
