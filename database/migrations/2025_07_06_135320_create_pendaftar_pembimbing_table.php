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
        Schema::create('pendaftar_pembimbing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('event')->onDelete('cascade');
            $table->foreignId('pembimbing_id')->constrained('pembimbing')->onDelete('cascade');
            $table->string('url_qrCode')->nullable();
            $table->string('status_kehadiran')->nullable();
            $table->dateTime('tanggal_kehadiran')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftar_pembimbing');
    }
};
