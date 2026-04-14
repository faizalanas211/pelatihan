<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::table('sertifikasi', function ($table) {
        $table->dropColumn('masa_berlaku'); // Hapus dari induk
    });
    Schema::table('sertifikasi_peserta', function ($table) {
        $table->date('masa_berlaku')->nullable()->after('nama_peserta'); // Tambah ke detail
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            //
        });
    }
};
