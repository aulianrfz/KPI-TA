<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('pendaftar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_kategori_id');
            $table->unsignedBigInteger('peserta_id');
            $table->string('url_qrCode',255)->nullable();
            $table->string('status', 25)->nullable();
            $table->timestamps();

            $table->foreign('sub_kategori_id')->references('id')->on('sub_kategori')->onDelete('cascade');
            $table->foreign('peserta_id')->references('id')->on('peserta')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('pendaftar');
    }
};
