<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeApprovedPpmpAndAppUpdatedToStringInProcurementsTable extends Migration
{
    public function up()
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->string('approved_ppmp')->nullable()->change();
            $table->string('app_updated')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->boolean('approved_ppmp')->nullable()->change();
            $table->boolean('app_updated')->nullable()->change();
        });
    }
}

