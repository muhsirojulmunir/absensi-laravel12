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
        Schema::table('meal_allowance_payments', function (Blueprint $table) {
            // Drop constraint if needed, or simply make it nullable.
            // On MySQL/PostgreSQL, we can modify the column to be nullable.
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('manual_employee_name')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meal_allowance_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn('manual_employee_name');
        });
    }
};
