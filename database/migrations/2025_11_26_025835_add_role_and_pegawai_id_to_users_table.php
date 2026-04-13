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
        Schema::table('users', function (Blueprint $table) {
            // foreign key ke tabel pegawai
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawai')->onDelete('cascade');

            // role untuk menentukan level user
            $table->string('role')->default('pegawai');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pegawai_id']);
            $table->dropColumn(['pegawai_id', 'role']);
        });
    }
};
