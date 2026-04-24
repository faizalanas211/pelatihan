<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Hapus data duplikat terlebih dahulu
        DB::statement('
            DELETE s1 FROM sertifikasi_peserta s1
            INNER JOIN sertifikasi_peserta s2 
            WHERE s1.id > s2.id 
            AND s1.sertifikasi_id = s2.sertifikasi_id 
            AND s1.nip = s2.nip
        ');
        
        // Tambahkan unique constraint
        Schema::table('sertifikasi_peserta', function (Blueprint $table) {
            $table->unique(['sertifikasi_id', 'nip'], 'unique_peserta_per_sertifikasi');
        });
    }

    public function down()
    {
        Schema::table('sertifikasi_peserta', function (Blueprint $table) {
            $table->dropUnique('unique_peserta_per_sertifikasi');
        });
    }
};