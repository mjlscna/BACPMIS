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
        Schema::table('bid_mode_of_procurement', function (Blueprint $table) {
            $table->softDeletes(); // Adds a nullable `deleted_at` column
        });
    }

};
