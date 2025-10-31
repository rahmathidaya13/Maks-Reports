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
        Schema::create('page_permissions', function (Blueprint $table) {
            $table->uuid('page_permissions_id')->primary();
            $table->foreignUuid('created_by')->nullable()->constrained('users', 'id')->onDelete('cascade');
            $table->foreignUuid('roles_id')->nullable()->constrained('roles', 'roles_id')->onDelete('cascade');
            $table->string('name'); // Contoh: Home, Report Teknisi, Laporan Bulanan
            $table->string('slug')->unique(); // contoh: home, report-teknisi

            $table->boolean('can_view')->default(false);
            $table->boolean('can_add')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_export')->default(false);
            $table->boolean('can_import')->default(false);
            $table->boolean('can_share')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_permissions');
    }
};
