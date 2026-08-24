<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah kolom foto bukti untuk pengajuan "Lupa Absen".
     * Foto diambil langsung dari kamera di counter dan sudah dicap
     * tanggal/jam + nama counter saat pengambilan.
     *
     * CATATAN: migration ini HANYA MENAMBAH kolom baru (nullable).
     * Tidak ada kolom/data lama yang diubah atau dihapus.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('reason');
            $table->timestamp('photo_taken_at')->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['photo', 'photo_taken_at']);
        });
    }
};
