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
    Schema::table('pelatihan_peserta', function (Blueprint $table) {
        $table->integer('jp')->nullable()->after('nama_peserta');
    });

    Schema::table('pelatihan', function (Blueprint $table) {
        $table->dropColumn('jp');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelatihan_peserta', function (Blueprint $table) {
            //
        });
    }
};
