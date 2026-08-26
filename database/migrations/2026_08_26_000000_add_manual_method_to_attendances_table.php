<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah dukungan "Absen Manual" (foto selfie + cap waktu, dipakai saat GPS/radius
     * bermasalah) dan pencatatan jarak radius saat absen, khusus Karyawan Ramayana.
     *
     * CATATAN: migration ini HANYA MENAMBAH kolom baru (nullable / default aman).
     * Tidak ada kolom atau data lama yang diubah maupun dihapus.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('check_in_method')->default('otomatis')->after('check_in');
            $table->string('check_out_method')->default('otomatis')->after('check_out');
            $table->string('check_in_photo')->nullable()->after('check_in_method');
            $table->string('check_out_photo')->nullable()->after('check_out_method');
            $table->integer('check_in_distance_meters')->nullable()->after('check_in_photo');
            $table->integer('check_out_distance_meters')->nullable()->after('check_out_photo');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_method',
                'check_out_method',
                'check_in_photo',
                'check_out_photo',
                'check_in_distance_meters',
                'check_out_distance_meters',
            ]);
        });
    }
};
