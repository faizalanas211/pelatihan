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
        Schema::table('tubel_peserta', function (Blueprint $table) {
            // Tambah kolom status
            if (!Schema::hasColumn('tubel_peserta', 'status')) {
                $table->enum('status', ['belum_selesai', 'selesai'])->default('belum_selesai')->after('tanggal_selesai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tubel_peserta', function (Blueprint $table) {
            if (Schema::hasColumn('tubel_peserta', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};