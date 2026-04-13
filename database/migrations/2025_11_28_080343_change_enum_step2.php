<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Convert data lama
        DB::statement("UPDATE pegawai SET jenis_kelamin='Laki-laki' WHERE jenis_kelamin='L'");
        DB::statement("UPDATE pegawai SET jenis_kelamin='Perempuan' WHERE jenis_kelamin='P'");

        // Hapus enum lama L dan P
        DB::statement("
            ALTER TABLE pegawai 
            MODIFY jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL
        ");
    }

    public function down()
    {
        // Kembalikan data (opsional)
        DB::statement("UPDATE pegawai SET jenis_kelamin='L' WHERE jenis_kelamin='Laki-laki'");
        DB::statement("UPDATE pegawai SET jenis_kelamin='P' WHERE jenis_kelamin='Perempuan'");

        DB::statement("
            ALTER TABLE pegawai 
            MODIFY jenis_kelamin ENUM('L', 'P') NOT NULL
        ");
    }

};
