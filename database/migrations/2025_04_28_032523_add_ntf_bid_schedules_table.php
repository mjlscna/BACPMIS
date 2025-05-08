<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ntf_bid_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procID'); // Foreign key to Procurement

            // Added fields after procID
            $table->string('ib_number')->nullable();
            $table->date('pre_proc_conference')->nullable();
            $table->date('ads_post_ib')->nullable();
            $table->date('pre_bid_conf')->nullable();
            $table->date('eligibility_check')->nullable();
            $table->date('sub_open_bids')->nullable();
            $table->string('bidding_number')->nullable();

            // Original NTF fields
            $table->string('ntf_no')->nullable();
            $table->date('ntf_bidding_date')->nullable();
            $table->string('ntf_bidding_result')->nullable();

            $table->string('rfq_no')->nullable();
            $table->date('canvass_date')->nullable();
            $table->date('date_returned_of_canvass')->nullable();
            $table->date('abstract_of_canvass_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ntf_bid_schedules');
    }
};
