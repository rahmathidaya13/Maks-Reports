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
        Schema::create('scraped_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('price')->nullable();
            $table->string('link', 500)->unique();
            $table->string('image_link', 500)->nullable();
            $table->string('category')->index(); // Untuk filter kategori cepat 

            $table->fullText('name');  // Untuk live search super cepat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraped_products');
    }
};
