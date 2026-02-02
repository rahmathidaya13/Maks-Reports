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
        Schema::create('membership_levels', function (Blueprint $table) {
            $table->uuid('membership_level_id')->primary();
            $table->foreignUuid('created_by')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('name', 50);
            $table->integer('discount_percentage');
            $table->unsignedBigInteger('min_transaction_value')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_levels');
    }
};
