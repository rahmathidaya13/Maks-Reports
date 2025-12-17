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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('transaction_id')->primary();
            $table->foreignUuid('customer_id')
                ->constrained('customers', 'customer_id')
                ->onDelete('cascade');

            $table->foreignUuid('sales_record_id')
                ->constrained('sales_records', 'sales_record_id')
                ->onDelete('cascade');

            $table->foreignUuid('product_id')
                ->constrained('products', 'product_id')
                ->onDelete('cascade');

            $table->unsignedBigInteger('price_original');
            $table->unsignedBigInteger('price_discount');

            $table->string('invoice', 50)->index();
            $table->enum('status', ['first_fund', 'paid_off'])->default('first_fund');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
