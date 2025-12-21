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
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->uuid('payment_id')->primary();

            $table->foreignUuid('created_by')
                ->index()
                ->constrained('users', 'id')
                ->onDelete('cascade');

            $table->foreignUuid('transaction_id')
                ->index()
                ->constrained('transactions', 'transaction_id')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('amount');

            $table->enum('payment_type', [
                'payment',
                'repayment'
            ]); // Pembayaran / pelunasan

            $table->enum('payment_method', [
                'cash',
                'transfer',
                'debit',
                'qris'
            ]);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');
    }
};
