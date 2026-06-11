<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_stock_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incoming_stock_id');
            $table->string('sku');
            $table->string('kode_barang')->nullable();
            $table->string('size')->nullable();
            $table->string('warna')->nullable();
            $table->string('satuan', 20)->default('PSG');
            $table->integer('qty');
            $table->timestamps();

            $table->foreign('incoming_stock_id')->references('id')->on('incoming_stocks')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_stock_items');
    }
};
