<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commit_collection_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('branch_name', 255);
            $table->timestamp('latest_committed_date');

            $table->primary(['project_id', 'branch_name'], 'commit_collection_histories_pkey');
            $table->index('project_id');
            $table->index('latest_committed_date');
            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commit_collection_histories');
    }
};
