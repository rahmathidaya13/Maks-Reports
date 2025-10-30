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
        Schema::create('user_page_permissions', function (Blueprint $table) {
            $table->uuid('user_page_permissions_id')->primary();
            $table->foreignUuid('users_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignUuid('pages_id')->constrained('pages', 'pages_id')->cascadeOnDelete();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_add')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_share')->default(false);
            $table->boolean('can_upload')->default(false);
            $table->boolean('can_import')->default(false);
            $table->boolean('can_export')->default(false);
            $table->boolean('can_access')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_page_permissions');
    }
};
