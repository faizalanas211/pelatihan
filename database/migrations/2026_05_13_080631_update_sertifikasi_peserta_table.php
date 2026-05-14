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
        Schema::table('sertifikasi_peserta', function (Blueprint $table) {
            // 1. Rename kolom tanggal_mulai menjadi tanggal_perolehan
            if (Schema::hasColumn('sertifikasi_peserta', 'tanggal_mulai')) {
                $table->renameColumn('tanggal_mulai', 'tanggal_perolehan');
            }
            
            // 2. Hapus kolom tanggal_selesai
            if (Schema::hasColumn('sertifikasi_peserta', 'tanggal_selesai')) {
                $table->dropColumn('tanggal_selesai');
            }
            
            // 3. Ubah kolom masa_berlaku dari date menjadi string
            if (Schema::hasColumn('sertifikasi_peserta', 'masa_berlaku')) {
                $table->string('masa_berlaku', 100)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sertifikasi_peserta', function (Blueprint $table) {
            // 1. Kembalikan nama kolom
            if (Schema::hasColumn('sertifikasi_peserta', 'tanggal_perolehan')) {
                $table->renameColumn('tanggal_perolehan', 'tanggal_mulai');
            }
            
            // 2. Tambah kembali kolom tanggal_selesai
            if (!Schema::hasColumn('sertifikasi_peserta', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable();
            }
            
            // 3. Kembalikan masa_berlaku ke date
            if (Schema::hasColumn('sertifikasi_peserta', 'masa_berlaku')) {
                $table->date('masa_berlaku')->nullable()->change();
            }
        });
    }
};