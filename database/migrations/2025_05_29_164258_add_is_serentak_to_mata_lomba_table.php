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
        Schema::table('mata_lomba', function (Blueprint $table) {
            $table->boolean('is_serentak')->default(false)->after('jenis_lomba');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('mata_lomba', function (Blueprint $table) {
            $table->dropColumn('is_serentak');
        });
    }
};
