<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('master_pelatihans', function (Blueprint $table) {
            $table->dropColumn('jp');
        });
    }

    public function down()
    {
        Schema::table('master_pelatihans', function (Blueprint $table) {
            $table->integer('jp')->nullable()->after('nama_pelatihan');
        });
    }
};