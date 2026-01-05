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
        Schema::create('product_price', function (Blueprint $table) {
            $table->uuid('product_price_id')->primary();
            $table->foreignUuid('product_id')
                ->constrained('products', 'product_id')
                ->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches', 'branches_id')->cascadeOnDelete();
            $table->enum('status', ['draft', 'published'])->default('published')->index();
            $table->date('valid_from')->nullable()->index(); // tanggal berlaku
            $table->date('valid_until')->nullable()->index(); // tanggal berakhir opsional

            $table->unsignedBigInteger('base_price')->default(0);
            $table->unsignedBigInteger('discount_price')->nullable()->default(0);

            $table->enum('price_type', ['normal', 'discount'])->default('normal');

            $table->index(['product_id', 'created_by', 'branch_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_price');
    }
};
