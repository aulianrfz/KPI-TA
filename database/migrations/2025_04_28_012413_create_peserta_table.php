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
        Schema::create('peserta', function (Blueprint $table) {
            $table->id();
            $table->string('nama_peserta', 100);
            $table->string('nim', 20);
            $table->string('prodi', 50);
            $table->string('institusi', 100);
            $table->string('provinsi',50);
            $table->string('jenis_peserta',10);

            $table->unsignedBigInteger('user_id');

            $table->string('email');
            $table->string('no_hp',15);
            $table->string('url_ktm',255);
            $table->string('url_ttd',255);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta');
    }
};
