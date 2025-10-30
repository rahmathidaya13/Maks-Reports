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
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('permissions_id')->primary();
            $table->foreignUuid('pages_id')->constrained('pages', 'pages_id')->onDelete('cascade');
            $table->string('key');    // 'can_view', 'can_add', 'can_edit', ...
            $table->string('label')->nullable(); // 'Lihat', 'Tambah'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
