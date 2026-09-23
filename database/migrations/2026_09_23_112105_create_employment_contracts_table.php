<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Data Karyawan (Pihak Kedua)
            $table->string('employee_name');
            $table->string('employee_nik')->nullable();
            $table->string('employee_birth_place')->nullable();
            $table->date('employee_birth_date')->nullable();
            $table->text('employee_address')->nullable();
            $table->string('employee_phone')->nullable();
            $table->string('employee_position');

            // Masa Kontrak
            $table->date('contract_start');
            $table->date('contract_end');

            // Gaji & Tunjangan
            $table->bigInteger('basic_salary')->default(0);
            $table->bigInteger('meal_allowance')->default(0);
            $table->bigInteger('transport_allowance')->default(0);
            $table->bigInteger('other_allowance')->default(0);
            $table->string('other_allowance_note')->nullable();

            // Tanda Tangan Perusahaan
            $table->string('company_representative')->default('NICOLAS EDO WIDJAJA');
            $table->string('company_representative_position')->default('PIC Online');
            $table->string('signed_city')->default('Surabaya');
            $table->date('signed_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_contracts');
    }
};
