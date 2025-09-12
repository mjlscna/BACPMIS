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
        Schema::create('pr_item_prstage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procID');
            $table->unsignedBigInteger('prItemID');
            $table->unsignedBigInteger('pr_stage_id'); // latest stage
            $table->text('stage_history')->nullable(); // e.g. "1-3-1-5-3-2-5-2"
            $table->timestamps();

            // Foreign keys with restrict delete
            $table->foreign('procID')
                ->references('id')
                ->on('procurements')
                ->restrictOnDelete();

            $table->foreign('prItemID')
                ->references('id')
                ->on('pr_items')   // connect to items table
                ->restrictOnDelete();

            $table->foreign('pr_stage_id')
                ->references('id')
                ->on('procurement_stages') // correct table name
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr_item_prstage');
    }
};
