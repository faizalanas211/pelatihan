<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Tambah kolom instansi di master_pelatihans
        Schema::table('master_pelatihans', function (Blueprint $table) {
            $table->string('instansi')->nullable()->after('jp');
        });

        // 2. Migrate data instansi dari tabel pelatihan ke master
        DB::statement('
            UPDATE master_pelatihans m
            JOIN pelatihan p ON p.master_pelatihan_id = m.id
            SET m.instansi = p.instansi_penyelenggara
            WHERE m.kategori = "pelatihan"
        ');

        // 3. Migrate data instansi dari tabel sertifikasi ke master
        DB::statement('
            UPDATE master_pelatihans m
            JOIN sertifikasi s ON s.master_pelatihan_id = m.id
            SET m.instansi = s.instansi_penerbit
            WHERE m.kategori = "sertifikasi"
        ');

        // 4. Hapus kolom instansi dari tabel pelatihan
        Schema::table('pelatihan', function (Blueprint $table) {
            $table->dropColumn('instansi_penyelenggara');
        });

        // 5. Hapus kolom instansi dari tabel sertifikasi
        Schema::table('sertifikasi', function (Blueprint $table) {
            $table->dropColumn('instansi_penerbit');
        });
    }

    public function down()
    {
        // Rollback: tambah kembali kolom yang dihapus
        Schema::table('pelatihan', function (Blueprint $table) {
            $table->string('instansi_penyelenggara')->nullable();
        });

        Schema::table('sertifikasi', function (Blueprint $table) {
            $table->string('instansi_penerbit')->nullable();
        });

        Schema::table('master_pelatihans', function (Blueprint $table) {
            $table->dropColumn('instansi');
        });
    }
};