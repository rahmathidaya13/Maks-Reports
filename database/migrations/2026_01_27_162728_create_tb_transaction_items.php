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
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->uuid('transaction_item_id')->primary();
            $table->foreignUuid('created_by')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignUuid('transaction_id')->constrained('transactions', 'transaction_id');
            $table->foreignUuid('product_id')->constrained('products', 'product_id');

            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('price_unit');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('subtotal'); // (price - discount) * qty
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
