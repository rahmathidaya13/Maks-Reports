<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_title', function (Blueprint $table) {
            $table->uuid('job_title_id')->primary();
            $table->foreignUuid('created_by')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('job_title_code', 25)->unique();
            $table->string('title', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->string('title_alias', 15)->unique();
            $table->text('description');
            $table->index(['created_by', 'job_title_code', 'title', 'slug']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_title');
    }
};
