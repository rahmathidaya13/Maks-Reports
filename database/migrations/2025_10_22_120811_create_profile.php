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
        Schema::create('profile', function (Blueprint $table) {
            $table->uuid("profile_id")->primary();

            $table->foreignUuid('users_id')
                ->constrained('users', 'id')
                ->cascadeOnDelete();

            $table->foreignUuid('branches_id')
                ->nullable()
                ->constrained('branches', 'branches_id')
                ->onDelete('set null');

            // --- Basic Information ---
            $table->string('employee_id_number', 13)->unique()
                ->nullable()->index();

            $table->string('national_id_number', 20)
                ->unique()->nullable()->index(); // NIK KTP

            $table->enum('religion', ['islam', 'kristen', 'katolik', 'hindu', 'budha', 'konghucu'])->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birthdate')->nullable();
            $table->string('birthplace', 100)->nullable();

            // --- Contact Information ---
            $table->string('number_phone', 13)->unique()->nullable();
            $table->string('address', 250)->nullable();

            // --- Employment Information ---
            $table->date('date_of_entry')->nullable();
            $table->enum('employee_status', [
                'contract',
                'permanent',
                'intern',
                'freelance'
            ])->default('contract');

            // --- Education Information ---
            $table->string('education', 50)->nullable();
            $table->string('major', 100)->nullable(); // jurusan pendidikan

            $table->text('images')->nullable();
            $table->boolean('is_completed')->default(false);


            $table->index(['users_id', 'branches_id']);
            $table->softDeletes();
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
