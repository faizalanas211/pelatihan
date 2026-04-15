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
        // Menambahkan kolom tanggal_selesai setelah waktu_pelaksanaan
        $table->date('tanggal_selesai')->after('waktu_pelaksanaan')->nullable();
    });
}

public function down()
{
    Schema::table('pelatihan', function (Blueprint $table) {
        $table->dropColumn('tanggal_selesai');
    });
}
};
