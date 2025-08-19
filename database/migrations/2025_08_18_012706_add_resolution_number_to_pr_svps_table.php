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
        Schema::table('pr_svps', function (Blueprint $table) {
            // Add column before `rfq_no` if using MySQL
            $table->string('resolution_number')->nullable()->after('uid');
        });
    }

    public function down()
    {
        Schema::table('pr_svps', function (Blueprint $table) {
            $table->dropColumn('resolution_number');
        });
    }

};
