<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UniqueMarkerName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("markers", function (Blueprint $table) {
            $table->unique(['name', 'user_id'], 'unique_marker_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("markers", function (Blueprint $table) {
            $table->dropUnique('unique_marker_name');
        });
    }
}
