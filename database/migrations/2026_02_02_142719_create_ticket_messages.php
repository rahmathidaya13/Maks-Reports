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
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->uuid('message_id')->primary();
            $table->foreignUuid('created_by')->constrained('users', 'id')->cascadeOnDelete(); // Siapa yang ngetik (User atau Admin)
            $table->foreignUuid('ticket_id')->constrained('tickets', 'ticket_id')->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_admin_reply')->default(false); // Penanda biar gampang styling (Kiri/Kanan)
            $table->string('attachment')->nullable(); // Foto bukti jika ada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
