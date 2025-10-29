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
        Schema::table('pr_svps', function (Blueprint $table) {
            // 1. ADD the new mop_id column, constrained to the mops table
            $table->foreignId('mop_id')
                ->nullable() // Keep nullable if needed
                ->after('id') // Adjust position if desired (e.g., after 'uid' if it exists)
                ->constrained('mops') // Links to mops.id
                ->cascadeOnDelete();

            // 2. DROP the old procID column (if it exists)
            // IMPORTANT: Check if 'procID' existed in this table and had a foreign key.
            // If yes, uncomment the next line and use the correct constraint name.
            // $table->dropForeign(['procID']); // e.g., $table->dropForeign('pr_svps_procid_foreign');

            // Uncomment this line if 'procID' existed and you want to drop it.
            $table->dropColumn('procID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pr_svps', function (Blueprint $table) {
            // 1. RE-ADD the procID column (if you dropped it in up())
            // Ensure type matches original (e.g., string)
            $table->string('procID')->nullable()->after('id'); // Adjust position

            // Re-add its foreign key if you dropped it in up()
            // $table->foreign('procID')->references('procID')->on('procurements')->cascadeOnDelete();

            // 2. DROP the mop_id column and its constraint
            $table->dropForeign(['mop_id']);
            $table->dropColumn('mop_id');
        });
    }
};
