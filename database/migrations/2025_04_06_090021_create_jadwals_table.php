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
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jadwal'); // Input dari user
            $table->year('tahun'); // Input dari user
            $table->string('kategori_lomba');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('venue');
            $table->string('peserta');
            $table->integer('version');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
