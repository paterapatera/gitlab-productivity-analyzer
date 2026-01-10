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
        Schema::create('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->text('description')->nullable();
            $table->string('name_with_namespace', 500);
            $table->string('default_branch', 255)->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
