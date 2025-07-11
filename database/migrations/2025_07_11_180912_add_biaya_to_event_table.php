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
        Schema::table('event', function (Blueprint $table) {
            $table->decimal('biaya', 12, 2)->default(0); // 12 digit total, 2 digit di belakang koma
        });
    }

    public function down()
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropColumn('biaya');
        });
    }


};
