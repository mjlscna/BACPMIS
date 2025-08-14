<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bid_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('procID'); // or use foreignId if it's an actual FK
            $table->string('uid')->nullable();
            $table->unsignedBigInteger('modeproc')->nullable(); // You can also make this a foreignId if referencing a table
            $table->string('ib_number')->nullable();
            $table->date('pre_proc_conference')->nullable();
            $table->date('ads_post_ib')->nullable();
            $table->date('pre_bid_conf')->nullable();
            $table->date('eligibility_check')->nullable();
            $table->date('sub_open_bids')->nullable();
            $table->string('bidding_number')->nullable();
            $table->date('bidding_date')->nullable();
            $table->string('bidding_result')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bid_schedules');
    }
};
