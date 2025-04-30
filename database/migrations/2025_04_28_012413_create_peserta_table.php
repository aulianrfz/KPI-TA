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
            $table->string('nama');
            $table->bigInteger('nim')->nullable();
            $table->string('nama_tim')->nullable();
            $table->string('jurusan_id')->nullable();
            $table->string('url_qrCode')->nullable();

            // Foreign Key Columns
            $table->unsignedBigInteger('provinsi_id');
            $table->unsignedBigInteger('institusi_id');
            $table->unsignedBigInteger('sub_kategori_id');
            $table->unsignedBigInteger('user_id');

            $table->string('email')->nullable();
            $table->bigInteger('hp')->nullable();
            $table->string('ktm_path')->nullable();
            $table->string('ttd_path')->nullable();
            $table->boolean('is_leader')->default(false);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('provinsi_id')->references('id')->on('provinsi')->onDelete('cascade');
            $table->foreign('institusi_id')->references('id')->on('institusi')->onDelete('cascade');
            $table->foreign('sub_kategori_id')->references('id')->on('sub_kategori')->onDelete('cascade');
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
