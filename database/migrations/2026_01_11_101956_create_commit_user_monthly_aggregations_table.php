<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commit_user_monthly_aggregations', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('branch_name', 255);
            $table->string('author_email', 255);
            $table->integer('year');
            $table->integer('month');
            $table->string('author_name', 255)->nullable();
            $table->integer('total_additions')->default(0);
            $table->integer('total_deletions')->default(0);
            $table->integer('commit_count')->default(0);
            $table->timestamp('created_at')->nullable(false);
            $table->timestamp('updated_at')->nullable(false);

            $table->primary(['project_id', 'branch_name', 'author_email', 'year', 'month'], 'commit_user_monthly_aggregations_pkey');
            $table->index(['project_id', 'branch_name'], 'idx_project_branch');
            $table->index(['year', 'month'], 'idx_year_month');
            $table->index('author_email', 'idx_author_email');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });

        // CHECK制約を追加
        DB::statement('ALTER TABLE commit_user_monthly_aggregations ADD CONSTRAINT check_year_range CHECK (year >= 1 AND year <= 9999)');
        DB::statement('ALTER TABLE commit_user_monthly_aggregations ADD CONSTRAINT check_month_range CHECK (month >= 1 AND month <= 12)');
        DB::statement('ALTER TABLE commit_user_monthly_aggregations ADD CONSTRAINT check_total_additions_non_negative CHECK (total_additions >= 0)');
        DB::statement('ALTER TABLE commit_user_monthly_aggregations ADD CONSTRAINT check_total_deletions_non_negative CHECK (total_deletions >= 0)');
        DB::statement('ALTER TABLE commit_user_monthly_aggregations ADD CONSTRAINT check_commit_count_non_negative CHECK (commit_count >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commit_user_monthly_aggregations');
    }
};
