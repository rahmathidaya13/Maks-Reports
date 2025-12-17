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
        Schema::create('sales_records', function (Blueprint $table) {
            $table->uuid('sales_record_id')->primary();
            $table->foreignUuid('created_by')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('sales_record_code', 25)->unique();
            $table->date('sale_date')->index();

            $table->enum('customer_source', [
                'walk_in',
                'whatsapp',
                'phone_call',
                'repeat_customer'
            ])->index();

            $table->enum('purchase_channel', [
                'in_store',
                'online',
                'phone_order'
            ])->index();
            
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_records');
    }
};
