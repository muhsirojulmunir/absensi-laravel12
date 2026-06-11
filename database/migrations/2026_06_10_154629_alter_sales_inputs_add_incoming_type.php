<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales_inputs MODIFY COLUMN type ENUM('stock_in', 'sale', 'incoming') DEFAULT 'sale'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales_inputs MODIFY COLUMN type ENUM('stock_in', 'sale') DEFAULT 'sale'");
    }
};
