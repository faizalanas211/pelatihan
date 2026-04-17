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
    Schema::table('pelatihan', function (Blueprint $table) {
        // Ini perintah untuk menghapus kolom yang sudah tidak terpakai
        $table->dropColumn(['waktu_pelaksanaan', 'tanggal_selesai']);
    });
}

public function down()
{
    Schema::table('pelatihan', function (Blueprint $table) {
        // Ini untuk mengembalikan kolom jika suatu saat kamu melakukan rollback
        $table->date('waktu_pelaksanaan')->nullable();
        $table->date('tanggal_selesai')->nullable();
    });
}
};
