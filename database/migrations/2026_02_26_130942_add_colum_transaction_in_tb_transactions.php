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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_total')->after('status');
            $table->unsignedBigInteger('tax_percentage')->after('sub_total');
            $table->unsignedBigInteger('tax_amount')->after('tax_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('sub_total');
            $table->dropColumn('tax_percentage');
            $table->dropColumn('tax_amount');
        });
    }
};
