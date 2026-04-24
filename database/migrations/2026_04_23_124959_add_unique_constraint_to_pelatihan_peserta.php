<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Hapus data duplikat terlebih dahulu
        DB::statement('
            DELETE p1 FROM pelatihan_peserta p1
            INNER JOIN pelatihan_peserta p2 
            WHERE p1.id > p2.id 
            AND p1.pelatihan_id = p2.pelatihan_id 
            AND p1.nip = p2.nip
        ');
        
        // 2. Tambahkan unique constraint
        Schema::table('pelatihan_peserta', function (Blueprint $table) {
            $table->unique(['pelatihan_id', 'nip'], 'unique_peserta_per_pelatihan');
        });
    }

    public function down()
    {
        Schema::table('pelatihan_peserta', function (Blueprint $table) {
            $table->dropUnique('unique_peserta_per_pelatihan');
        });
    }
};