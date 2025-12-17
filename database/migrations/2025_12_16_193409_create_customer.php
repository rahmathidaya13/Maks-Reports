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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('customer_id')->primary();

            // 🔑 relasi ke sales (user)
            $table->foreignUuid('sales_id')
                ->constrained('users', 'id')
                ->cascadeOnDelete();
                
            $table->string('national_id_number', 20)
                ->unique()
                ->nullable()
                ->index(); // NIK KTP
            $table->string('customer_name', 50)->index();
            $table->string('number_phone_customer', 13)->index();
            $table->string('city', 50);
            $table->string('province', 50);
            $table->text('address');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
