<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->boolean('kwitansi_individu')->default(false);
            $table->boolean('kwitansi_cap_basah')->default(false);
        });

        Schema::table('pembayaran_pembimbing', function (Blueprint $table) {
            $table->boolean('kwitansi_individu')->default(false);
            $table->boolean('kwitansi_cap_basah')->default(false);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            //
        });
    }
};
