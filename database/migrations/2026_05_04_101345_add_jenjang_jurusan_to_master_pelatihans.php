<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('master_pelatihans', function (Blueprint $table) {
            $table->string('jenjang')->nullable()->after('nama_pelatihan');
            $table->string('jurusan')->nullable()->after('jenjang');
        });
    }

    public function down()
    {
        Schema::table('master_pelatihans', function (Blueprint $table) {
            $table->dropColumn(['jenjang', 'jurusan']);
        });
    }
};