<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CorrectUidToBidModeOfProcurement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Step 1: Drop the existing `uid` column if it exists (incorrect position)
        Schema::table('bid_mode_of_procurement', function (Blueprint $table) {
            if (Schema::hasColumn('bid_mode_of_procurement', 'uid')) {
                $table->dropColumn('uid');
            }
        });

        // Step 2: Add the `uid` column in the correct position (after `procID`)
        Schema::table('bid_mode_of_procurement', function (Blueprint $table) {
            $table->string('uid')->nullable()->after('procID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the `uid` column if rolling back
        Schema::table('bid_mode_of_procurement', function (Blueprint $table) {
            $table->dropColumn('uid');
        });

        // Optionally, re-add the `uid` column in its original position (after `procID`)
        Schema::table('bid_mode_of_procurement', function (Blueprint $table) {
            $table->string('uid')->nullable()->after('procID');
        });
    }
}
