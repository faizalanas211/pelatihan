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
    Schema::create('sertifikasi_peserta', function ($table) {
        $table->id();
        $table->unsignedBigInteger('sertifikasi_id');
        $table->string('nip');
        $table->string('nama_peserta');
        $table->string('sertifikat_path')->nullable();
        $table->timestamps();
        $table->foreign('sertifikasi_id')->references('id')->on('sertifikasi')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sertifikasi_peserta');
    }
};
