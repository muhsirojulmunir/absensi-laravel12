<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_inputs', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'sales_inputs_user_id_date_index');
            $table->index(['sku', 'satuan'], 'sales_inputs_sku_satuan_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales_inputs', function (Blueprint $table) {
            $table->dropIndex('sales_inputs_user_id_date_index');
            $table->dropIndex('sales_inputs_sku_satuan_index');
        });
    }
};
