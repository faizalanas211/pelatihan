<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('pelatihan', function (Blueprint $table) {
        // Kita tambahkan kolom jp setelah kolom tahun
        $table->integer('jp')->nullable()->after('tahun');
    });
}

public function down(): void
{
    Schema::table('pelatihan', function (Blueprint $table) {
        $table->dropColumn('jp');
    });
}
};
