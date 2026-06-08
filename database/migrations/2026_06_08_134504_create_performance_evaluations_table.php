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
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->json('attendance_summary');
            $table->json('sales_summary')->nullable(); // For Ramayana SPG/Sales
            $table->string('predicate'); // Sangat Baik, Baik, Cukup, Kurang
            $table->text('ai_feedback');
            $table->text('ai_recommendation')->nullable();
            $table->timestamps();
            
            // Ensure unique evaluation per user per month
            $table->unique(['user_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};
