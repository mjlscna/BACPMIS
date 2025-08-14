<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAbc50kColumnTypeInProcurementsTable extends Migration
{
    public function up()
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->string('abc_50k')->nullable()->change(); // Allows "50k or less", "above 50k"
        });
    }

    public function down()
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->boolean('abc_50k')->nullable()->change(); // Revert if needed
        });
    }
}

