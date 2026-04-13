<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();

            $table->string('nama_peminjam');
            $table->string('kelas')->nullable();

            $table->foreignId('barang_id')
                  ->constrained('barangs')
                  ->onDelete('cascade');

            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali')->nullable();

            $table->enum('status', ['dipinjam', 'dikembalikan'])
                  ->default('dipinjam');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
