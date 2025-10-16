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
        Schema::create('schedule_for_pr', function (Blueprint $table) {
            $table->id();
            $table->string('ib_number')->unique()->comment('Invitation to Bid No.');
            $table->date('opening_of_bids');
            $table->text('project_name');
            $table->boolean('is_framework')->default(false);
            $table->foreignId('status_id');
            $table->text('action_taken');
            $table->date('next_bidding_schedule')->nullable();
            $table->text('google_drive_link')->nullable();
            $table->decimal('ABC', 15, 2);
            $table->integer('no_items_lot')->nullable();
            $table->integer('pr_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
