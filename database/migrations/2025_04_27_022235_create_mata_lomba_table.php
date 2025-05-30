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
        Schema::create('mata_lomba', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kategori_id');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->string('nama_lomba',100);
            $table->string('jenis_lomba',25);
            $table->string('jurusan',50);
            $table->integer('min_peserta');
            $table->integer('maks_peserta');
            $table->integer('maks_total_peserta');
            $table->string('jenis_pelaksanaan',50);
            $table->text('deskripsi');
            $table->integer('durasi');
            $table->decimal('biaya_pendaftaran', 12, 2);
            $table->string('url_tor',255)->nullable();
            $table->string('foto_kompetisi',255);
            $table->timestamps();

            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('cascade');
            $table->foreign('venue_id')->references('id')->on('venue')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_lomba');
    }
};
