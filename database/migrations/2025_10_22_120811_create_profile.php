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
        Schema::create('profile', function (Blueprint $table) {
            $table->uuid("profile_id")->primary();
            $table->foreignUuid('users_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignUuid('roles_id')->nullable()->constrained('roles', 'roles_id')->onDelete('set null');
            $table->foreignUuid('branches_id')->nullable()->constrained('branches', 'branches_id')->onDelete('set null');
            $table->date('date_of_entry')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('education', 25)->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('number_phone', 13)->nullable();
            $table->string('address', 250)->nullable();
            $table->text('images')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile');
    }
};
