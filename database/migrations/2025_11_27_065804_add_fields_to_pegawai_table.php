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
        Schema::table('pegawai', function (Blueprint $table) {
            // Tambah kolom baru
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('nip');
            $table->string('tempat_lahir')->nullable()->after('jenis_kelamin');
            $table->string('pangkat_golongan')->nullable()->after('jabatan');

            // Hapus kolom lama
            $table->dropColumn(['is_pejabat', 'jenis_pejabat']);
        });
    }

    public function down()
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->boolean('is_pejabat')->default(0);
            $table->string('jenis_pejabat')->nullable();

            $table->dropColumn([
                'jenis_kelamin',
                'tempat_lahir',
                'pangkat_golongan',
            ]);
        });
    }
};
