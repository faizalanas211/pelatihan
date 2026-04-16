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
        Schema::table('tubel_peserta', function (Blueprint $table) {
            $table->string('no_sk_tubel')->nullable()->change();
            $table->string('file_sk_tubel')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tubel_peserta', function (Blueprint $table) {
            $table->string('no_sk_tubel')->nullable(false)->change();
            $table->string('file_sk_tubel')->nullable(false)->change();
        });
    }
};
