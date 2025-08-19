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
        Schema::table('venue', function (Blueprint $table) {
            $table->date('tanggal_tersedia')->nullable();
            $table->time('waktu_mulai_tersedia')->nullable();
            $table->time('waktu_berakhir_tersedia')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('venue', function (Blueprint $table) {
            $table->dropColumn(['tanggal_tersedia', 'waktu_mulai_tersedia', 'waktu_berakhir_tersedia']);
        });
    }
};
