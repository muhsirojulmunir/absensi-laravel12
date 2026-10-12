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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'nickname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nickname')->nullable()->after('name');
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'is_locked')) {
                    $table->boolean('is_locked')->default(false)->after('status');
                }
                if (!Schema::hasColumn('attendances', 'locked_by')) {
                    $table->string('locked_by')->nullable()->after('is_locked');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'nickname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('nickname');
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'locked_by')) {
                    $table->dropColumn('locked_by');
                }
                if (Schema::hasColumn('attendances', 'is_locked')) {
                    $table->dropColumn('is_locked');
                }
            });
        }
    }
};
