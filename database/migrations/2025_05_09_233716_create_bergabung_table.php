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
        Schema::create('bergabung', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tim_id');
            $table->unsignedBigInteger('peserta_id');
            $table->string('posisi', 50);


            $table->foreign('tim_id')->references('id')->on('tim')->onDelete('cascade');
            $table->foreign('peserta_id')->references('id')->on('peserta')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bergabung');
    }
};
