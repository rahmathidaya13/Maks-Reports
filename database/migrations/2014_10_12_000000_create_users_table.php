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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('google_id', 50)->nullable();
            $table->string('name', 50);
            $table->string('email', 50)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['developer', 'admin', 'user'])->default('user');
            $table->boolean('is_active')->default(false);
            $table->boolean('can_view')->default(true);
            $table->boolean('can_add')->default(true);
            $table->boolean('can_edit')->default(true);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_share')->default(false);
            $table->boolean('can_upload')->default(false);
            $table->boolean('can_import')->default(false);
            $table->boolean('can_export')->default(false);
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
