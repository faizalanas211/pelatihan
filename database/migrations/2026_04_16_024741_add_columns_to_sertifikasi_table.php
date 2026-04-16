<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('sertifikasi', function (Blueprint $table) {
        // Menambah kolom master_pelatihan_id untuk relasi ke master data
        $table->unsignedBigInteger('master_pelatihan_id')->nullable()->after('id');
        
        // Menambah kolom tanggal_selesai untuk rentang waktu & status otomatis
        $table->date('tanggal_selesai')->nullable()->after('tgl_terbit');
        
        // Opsional: Jika kolom status belum ada, tambahkan juga
        if (!Schema::hasColumn('sertifikasi', 'status')) {
            $table->string('status')->default('selesai')->after('tanggal_selesai');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sertifikasi', function (Blueprint $table) {
            //
        });
    }
};
