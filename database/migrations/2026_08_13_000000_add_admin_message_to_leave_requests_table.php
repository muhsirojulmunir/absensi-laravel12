<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah kolom pesan dari Super Admin / PIC ke karyawan pada pengajuan izin.
     * CATATAN: migration ini HANYA MENAMBAH kolom baru (nullable), tidak mengubah
     * maupun menghapus kolom/data yang sudah ada.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->text('admin_message')->nullable()->after('status');
            $table->foreignId('admin_message_by')->nullable()->after('admin_message')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('admin_message_at')->nullable()->after('admin_message_by');
            $table->timestamp('admin_message_read_at')->nullable()->after('admin_message_at');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_message_by');
            $table->dropColumn(['admin_message', 'admin_message_at', 'admin_message_read_at']);
        });
    }
};
