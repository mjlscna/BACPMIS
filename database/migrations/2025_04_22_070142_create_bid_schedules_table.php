<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bid_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('procID'); // Assuming it's a varchar as per your setup

            $table->string('ib_number')->nullable();
            $table->date('pre_proc_conference')->nullable();
            $table->date('ads_post_ib')->nullable();
            $table->date('pre_bid_conf')->nullable();
            $table->date('eligibility_check')->nullable();
            $table->date('sub_open_bids')->nullable();
            $table->unsignedTinyInteger('bidding_number')->nullable(); // 1 or 2
            $table->date('bidding_date')->nullable();
            $table->string('bidding_result')->nullable(); // SUCCESSFUL or UNSUCCESSFUL

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bid_schedules');
    }
};
