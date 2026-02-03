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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('ticket_id')->primary();
            $table->foreignUuid('created_by')->constrained('users', 'id')->cascadeOnDelete(); // User Cabang
            $table->string('subject'); // Judul Masalah
            $table->enum('category', ['general', 'technical', 'billing'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['open', 'answered', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
