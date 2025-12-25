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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('product_id')->primary();
            // pembuat data (boleh null)
            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users', 'id')
                ->nullOnDelete();

            $table->enum('source', ['manual', 'scrape'])->default('manual')->index();

            // nama produk, penting untuk pencarian
            $table->string('name')->index();
            $table->string('category')->index(); // Untuk filter kategori cepat

            // harga disimpan dalam integer (Rupiah)
            $table->unsignedBigInteger('price_original')->nullable()->index();
            $table->unsignedBigInteger('price_discount')->nullable()->index();

            $table->string('image_path')->nullable();
            // link bersifat unik agar tidak scrape ganda
            $table->string('link')->nullable();
            $table->string('image_link')->nullable();
            $table->json('image_url')->nullable();


            $table->longText('description')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
