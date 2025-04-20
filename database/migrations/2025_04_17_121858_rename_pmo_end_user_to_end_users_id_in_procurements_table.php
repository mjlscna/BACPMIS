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
        Schema::table('procurements', function (Blueprint $table) {
            $table->renameColumn('pmo_end_user', 'end_users_id');
        });
    }

    public function down()
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->renameColumn('end_users_id', 'pmo_end_user');
        });
    }

};
