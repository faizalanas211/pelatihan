<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Isi nilai NULL dulu agar tidak error
        DB::statement("UPDATE pegawai SET jenis_kelamin = 'L' WHERE jenis_kelamin IS NULL");

        // 2. Baru ubah tipe kolom
        DB::statement("
            ALTER TABLE pegawai
            MODIFY jenis_kelamin ENUM('L', 'P', 'Laki-laki', 'Perempuan') NOT NULL
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE pegawai
            MODIFY jenis_kelamin ENUM('L', 'P') NOT NULL
        ");
    }
};
