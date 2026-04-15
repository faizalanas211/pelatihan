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
        Schema::create('tubel_peserta', function (Blueprint $table) {
            $table->id();

            // relasi ke master_pelatihans
            $table->foreignId('master_pelatihan_id')
                ->constrained('master_pelatihans')
                ->cascadeOnDelete();

            // relasi ke pegawai
            $table->foreignId('pegawai_id')
                ->constrained('pegawai')
                ->cascadeOnDelete();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->string('no_sk_tubel');
            $table->string('file_sk_tubel');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tubel_peserta');
    }
};
