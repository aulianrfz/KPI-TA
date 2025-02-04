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
        Schema::create('all_peserta', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ketua')->nullable();
            $table->text('nama_anggota')->nullable(); // bisa pakai json juga kalau perlu
            $table->string('nomor_telepon')->nullable();
            $table->string('nama_institusi')->nullable();
            $table->text('keterangan')->nullable(); // bisa kosong
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('all_peserta');
    }
};
