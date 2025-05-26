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
        Schema::create('membayar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('peserta_id');
            $table->string('bukti_pembayaran')->nullable();
            $table->string('status', 25)->nullable();
            $table->dateTime('waktu')->default(now());
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoice')->onDelete('cascade');
            $table->foreign('peserta_id')->references('id')->on('peserta')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membayar');
    }
};
