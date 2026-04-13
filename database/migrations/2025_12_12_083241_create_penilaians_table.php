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
        Schema::create('penilaians', function (Blueprint $table) {
             $table->id();

        // FK pegawai outsourcing
            $table->unsignedBigInteger('alihdayas_id');

        // periode
            $table->string('bulan');
            $table->integer('tahun');

        // aspek penilaian
            $table->integer('kedisiplinan');
            $table->text('kedisiplinan_catatan')->nullable();
            $table->integer('tanggung_jawab');
        $table->text('tanggung_jawab_catatan')->nullable();

        $table->integer('sikap');
        $table->text('sikap_catatan')->nullable();

        $table->integer('kompetensi');
        $table->text('kompetensi_catatan')->nullable();

        $table->integer('penampilan');
        $table->text('penampilan_catatan')->nullable();

        $table->integer('komunikasi');
        $table->text('komunikasi_catatan')->nullable();

        $table->integer('kepatuhan_vendor');
        $table->text('kepatuhan_vendor_catatan')->nullable();

        // rekomendasi
        $table->string('rekomendasi');
        $table->text('rekomendasi_lain')->nullable();

        // penilai
        $table->string('penilai_nama');
        $table->string('penilai_jabatan');
        $table->date('tanggal');

        $table->timestamps();
        });
    }

};
