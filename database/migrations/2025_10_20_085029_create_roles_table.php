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
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid("roles_id")->primary();
            $table->foreignUuid('created_by')->nullable()->constrained('users', 'id')->onDelete('cascade');
            $table->string('name')->unique(); // contoh: Manager, SPV, Admin, Sales, Teknisi
            $table->string('description')->nullable(); // penjelasan singkat
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
