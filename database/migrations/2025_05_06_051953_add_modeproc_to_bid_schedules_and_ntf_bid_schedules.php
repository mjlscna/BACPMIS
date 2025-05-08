<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModeprocToBidSchedulesAndNtfBidSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bid_schedules', function (Blueprint $table) {
            $table->string('modeproc')->nullable()->after('procID');
        });

        Schema::table('ntf_bid_schedules', function (Blueprint $table) {
            $table->string('modeproc')->nullable()->after('procID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Dropping modeproc column from bid_schedules table
        Schema::table('bid_schedules', function (Blueprint $table) {
            $table->dropColumn('modeproc');
        });

        // Dropping modeproc column from ntf_bid_schedules table
        Schema::table('ntf_bid_schedules', function (Blueprint $table) {
            $table->dropColumn('modeproc');
        });
    }
}
