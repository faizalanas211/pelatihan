<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('sertifikasi', function (Blueprint $table) {
        $table->dropColumn(['tgl_terbit', 'tanggal_selesai']);
    });
}

public function down()
{
    Schema::table('sertifikasi', function (Blueprint $table) {
        $table->date('tgl_terbit')->nullable();
        $table->date('tanggal_selesai')->nullable();
    });
}
};
