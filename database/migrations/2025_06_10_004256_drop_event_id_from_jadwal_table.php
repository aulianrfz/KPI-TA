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
        // Schema::table('jadwal', function (Blueprint $table) {
        //     $table->dropForeign(['event_id']); // kalau ada foreign key
        //     $table->dropColumn('event_id');
        // });
    }

    public function down(): void
    {
        // Schema::table('jadwal', function (Blueprint $table) {
        //     $table->unsignedBigInteger('event_id')->nullable();

        //     // kalau mau restore foreign key:
        //     $table->foreign('event_id')->references('id')->on('event')->onDelete('cascade');
        // });
    }
};
