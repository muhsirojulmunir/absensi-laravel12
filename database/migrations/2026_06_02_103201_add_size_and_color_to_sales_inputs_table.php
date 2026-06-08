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
        Schema::table('sales_inputs', function (Blueprint $table) {
            $table->string('size')->nullable()->after('sku');
            $table->string('warna')->nullable()->after('size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_inputs', function (Blueprint $table) {
            $table->dropColumn(['size', 'warna']);
        });
    }
};
