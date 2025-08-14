<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidModeOfProcurementTable extends Migration
{
    public function up()
    {
        Schema::create('bid_mode_of_procurement', function (Blueprint $table) {
            $table->id();
            $table->string('procID');                 // Assuming a string like "BAC20250731012447"
            $table->string('uid');                    // Example: "MOP1-0"
            $table->unsignedBigInteger('mode_of_procurement_id'); // Assuming relation to a procurement mode
            $table->string('mode_order');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bid_mode_of_procurement');
    }
}

