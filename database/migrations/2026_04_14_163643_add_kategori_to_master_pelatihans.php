<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up() {
    Schema::table('master_pelatihans', function (Blueprint $table) {
        $table->string('kategori')->default('pelatihan')->after('id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_pelatihans', function (Blueprint $table) {
            //
        });
    }
};
