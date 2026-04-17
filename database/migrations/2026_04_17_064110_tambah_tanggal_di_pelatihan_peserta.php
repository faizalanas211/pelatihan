<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pelatihan_peserta', function (Blueprint $table) {
            // Menambah kolom tanggal mulai dan selesai setelah kolom nama_peserta
            $table->date('tanggal_mulai')->nullable()->after('nama_peserta');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
        });
    }

    public function down()
    {
        Schema::table('pelatihan_peserta', function (Blueprint $table) {
            // Untuk membatalkan (rollback) jika diperlukan
            $table->dropColumn(['tanggal_mulai', 'tanggal_selesai']);
        });
    }
};