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
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->nullable()->unique();
            $table->string('category', 100)->index();


            $table->text('image_path')->nullable();

            // link bersifat unik agar tidak scrape ganda
            $table->string('link', 500)->nullable();
            $table->string('image_link', 500)->nullable();

            $table->unique(['link', 'source']);
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
