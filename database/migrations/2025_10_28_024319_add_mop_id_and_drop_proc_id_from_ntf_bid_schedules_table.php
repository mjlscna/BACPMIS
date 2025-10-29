<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // In your migration file:
    public function up(): void
    {
        Schema::table('ntf_bid_schedules', function (Blueprint $table) {
            // 1. ADD mop_id
            $table->foreignId('mop_id')
                ->nullable()
                ->after('id') // Or wherever you want it
                ->constrained('mops')
                ->cascadeOnDelete();

            // 2. DROP procID
            // UNCOMMENT and use correct constraint name IF procID had a foreign key
            // $table->dropForeign(['procID']); // e.g., $table->dropForeign('ntf_bid_schedules_procid_foreign');

            // UNCOMMENT this line
            $table->dropColumn('procID'); // <<<< MAKE SURE THIS IS UNCOMMENTED
        });
    }

    public function down(): void
    {
        Schema::table('ntf_bid_schedules', function (Blueprint $table) {
            // 1. RE-ADD procID (because up() drops it)
            $table->string('procID')->nullable()->after('id'); // Adjust type/position

            // Re-add foreign key IF you dropped it in up()
            // $table->foreign('procID')->references('procID')->on('procurements')->cascadeOnDelete();

            // 2. DROP mop_id (because up() adds it)
            $table->dropForeign(['mop_id']);
            $table->dropColumn('mop_id');
        });
    }
};
