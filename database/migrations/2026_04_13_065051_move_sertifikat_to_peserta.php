<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tambah kolom sertifikat di tabel anak (peserta)
        Schema::table('pelatihan_peserta', function (Blueprint $table) {
            if (!Schema::hasColumn('pelatihan_peserta', 'sertifikat_path')) {
                $table->string('sertifikat_path')->nullable()->after('nama_peserta');
            }
        });
        
        // 2. Hapus kolom sertifikat di tabel induk (pelatihan) karena sudah tidak dipakai di sana
        Schema::table('pelatihan', function (Blueprint $table) {
            if (Schema::hasColumn('pelatihan', 'sertifikat_path')) {
                $table->dropColumn('sertifikat_path');
            }
        });
    }

    public function down()
    {
        Schema::table('pelatihan', function (Blueprint $table) {
            $table->string('sertifikat_path')->nullable();
        });

        Schema::table('pelatihan_peserta', function (Blueprint $table) {
            $table->dropColumn('sertifikat_path');
        });
    }
};