<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Hapus data duplikat terlebih dahulu
        DB::statement('
            DELETE m1 FROM master_pelatihans m1
            INNER JOIN master_pelatihans m2 
            WHERE m1.id > m2.id 
            AND m1.nama_pelatihan = m2.nama_pelatihan 
            AND m1.tahun = m2.tahun 
            AND m1.kategori = m2.kategori
        ');
        
        // 2. Tambahkan unique constraint
        Schema::table('master_pelatihans', function (Blueprint $table) {
            $table->unique(['nama_pelatihan', 'tahun', 'kategori'], 'unique_master_pelatihan');
        });
    }

    public function down()
    {
        Schema::table('master_pelatihans', function (Blueprint $table) {
            $table->dropUnique('unique_master_pelatihan');
        });
    }
};