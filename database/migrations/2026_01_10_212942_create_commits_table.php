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
        Schema::create('commits', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('branch_name', 255);
            $table->string('sha', 40);
            $table->text('message')->nullable();
            $table->timestamp('committed_date');
            $table->string('author_name', 255)->nullable();
            $table->string('author_email', 255)->nullable();
            $table->integer('additions')->default(0);
            $table->integer('deletions')->default(0);
            $table->timestamps();

            $table->primary(['project_id', 'branch_name', 'sha'], 'commits_pkey');
            $table->index('project_id');
            $table->index('committed_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commits');
    }
};
