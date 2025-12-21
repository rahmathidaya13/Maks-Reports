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
                ->index()
                ->constrained('customers', 'customer_id')
                ->onDelete('cascade');

            // Sales / user yang membuat transaksi
            $table->foreignUuid('created_by')
                ->index()
                ->constrained('users', 'id')
                ->onDelete('cascade');

            $table->foreignUuid('product_id')
                ->index()
                ->constrained('products', 'product_id')
                ->onDelete('cascade');

            // Snapshot harga saat transaksi
            $table->unsignedBigInteger('price_original');
            $table->unsignedBigInteger('price_discount')->default(0);
            $table->unsignedBigInteger('price_final');

            // Status bisnis
            $table->enum('status', [
                'payment',
                'repayment',
            ])->default('payment');

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
