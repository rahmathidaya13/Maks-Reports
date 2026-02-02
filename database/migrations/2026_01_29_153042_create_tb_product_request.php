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
        Schema::create('product_request', function (Blueprint $table) {
            $table->uuid('product_request_id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'id')->onDelete('cascade'); // Siapa yang minta (Cabang)
            $table->foreignUuid('product_id')->nullable()->constrained('products', 'product_id')->onDelete('set null'); // Produk apa
            $table->unsignedBigInteger('requested_price')->default(0); // Harga yang diminta user
            $table->text('reason'); // Alasan (Wajib diisi agar Admin paham)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Status tiket
            $table->text('admin_note')->nullable(); // Balasan admin (misal: "Oke di-acc tapi ambil min 10 qty")
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_request');
    }
};
