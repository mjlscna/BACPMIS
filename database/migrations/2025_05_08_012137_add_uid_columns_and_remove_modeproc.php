<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUidColumnsAndRemoveModeproc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the `uid` column to `bid_schedules` table
        Schema::table('bid_schedules', function (Blueprint $table) {
            $table->string('uid')->nullable()->after('procID');
            $table->dropColumn('modeproc');  // Remove the `modeproc` column
        });

        // Add the `uid` column to `ntf_bid_schedules` table
        Schema::table('ntf_bid_schedules', function (Blueprint $table) {
            $table->string('uid')->nullable()->after('procID');
            $table->dropColumn('modeproc');  // Remove the `modeproc` column
        });

        // Add the `uid` column to `bid_mode_of_procurement` table
        Schema::table('bid_mode_of_procurement', function (Blueprint $table) {
            $table->string('uid')->nullable()->after('mode_of_procurement_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the `uid` column from `bid_schedules` table and restore `modeproc`
        Schema::table('bid_schedules', function (Blueprint $table) {
            $table->dropColumn('uid');
            $table->string('modeproc')->nullable()->after('procID');
        });

        // Remove the `uid` column from `ntf_bid_schedules` table and restore `modeproc`
        Schema::table('ntf_bid_schedules', function (Blueprint $table) {
            $table->dropColumn('uid');
            $table->string('modeproc')->nullable()->after('procID');
        });

        // Remove the `uid` column from `bid_mode_of_procurement` table
        Schema::table('bid_mode_of_procurement', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
}
