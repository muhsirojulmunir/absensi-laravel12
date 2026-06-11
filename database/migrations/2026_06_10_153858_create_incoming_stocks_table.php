<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // counter / karyawan ramayana
            $table->date('date');
            $table->string('note')->nullable();
            $table->integer('total_items')->default(0);
            $table->decimal('total_qty', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable(); // super admin yang input
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_stocks');
    }
};
