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
        Schema::create('daily_report', function (Blueprint $table) {
            $table->uuid('daily_report_id')->primary();
            $table->foreignUuid('created_by')->constrained('users', 'id')->cascadeOnDelete();
            $table->date('date')->index(); // tanggal laporan
            $table->integer('leads')->default(0); // jumlah leads
            $table->integer('closing')->default(0); // jumlah closing
            $table->integer('fu_yesterday')->default(0); // follow up konsumen kemarin
            $table->integer('fu_yesterday_closing')->default(0); // closing dari FU kemarin
            $table->integer('fu_before_yesterday')->default(0); // follow up kemarennya
            $table->integer('fu_before_yesterday_closing')->default(0); // closing-nya
            $table->integer('fu_last_week')->default(0); // follow up minggu kemarennya
            $table->integer('fu_last_week_closing')->default(0); // closing-nya
            $table->integer('engage_old_customer')->default(0); // engage pelanggan lama
            $table->integer('engage_closing')->default(0); //closing-nya
            $table->softDeletes();
            $table->index(['created_by', 'date', 'created_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report');
    }
};
