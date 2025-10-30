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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->uuid('role_permissions_id')->primary();
            $table->foreignUuid('roles_id')->constrained('roles', 'roles_id')->cascadeOnDelete();
            $table->string('permission_key');
            $table->boolean('value')->default(false);
            $table->unique(['roles_id', 'permission_key']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
