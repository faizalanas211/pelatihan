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
        // 1. Tabel Induk: Menyimpan detail Event Pelatihan
        // Satu baris di sini mewakili satu sertifikat / satu kegiatan
        Schema::create('pelatihan', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_pelatihan');
            $table->year('tahun');
            $table->date('waktu_pelaksanaan');
            $table->integer('jp');
            $table->string('instansi_penyelenggara');
            $table->string('sertifikat_path')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Anak: Menyimpan daftar orang yang ikut di event tersebut
        // Menghubungkan ke tabel pelatihan melalui pelatihan_id
        Schema::create('pelatihan_peserta', function (Blueprint $table) {
            $table->id();
            // onDelete('cascade') artinya jika event pelatihan dihapus, daftar pesertanya otomatis terhapus
            $table->foreignId('pelatihan_id')->constrained('pelatihan')->onDelete('cascade');
            $table->string('nip');
            $table->string('nama_peserta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus tabel anak dulu baru tabel induk untuk menghindari error foreign key constraint
        Schema::dropIfExists('pelatihan_peserta');
        Schema::dropIfExists('pelatihan');
    }
};