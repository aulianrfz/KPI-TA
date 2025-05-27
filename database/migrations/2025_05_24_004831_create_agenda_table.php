<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal')->onDelete('cascade');

            $table->unsignedBigInteger('mata_lomba_id')->nullable();
            $table->foreign('mata_lomba_id')->references('id')->on('mata_lomba')->onDelete('set null');

            $table->date('tanggal')->nullable();

            $table->time('waktu_mulai');
            $table->time('waktu_selesai');

            $table->unsignedBigInteger('venue_id')->nullable();
            $table->foreign('venue_id')->references('id')->on('venue')->onDelete('set null');

            $table->unsignedBigInteger('peserta_id')->nullable();
            $table->foreign('peserta_id')->references('id')->on('peserta')->onDelete('set null');

            $table->unsignedBigInteger('tim_id')->nullable();
            $table->foreign('tim_id')->references('id')->on('tim')->onDelete('set null');

            $table->unsignedBigInteger('juri_id')->nullable();
            $table->foreign('juri_id')->references('id')->on('juri')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda');
    }
};
