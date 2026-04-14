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
        // default 'mendatang', bisa diubah ke 'selesai'
        $table->string('status')->default('mendatang')->after('instansi_penyelenggara');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelatihan', function (Blueprint $table) {
            //
        });
    }
};
