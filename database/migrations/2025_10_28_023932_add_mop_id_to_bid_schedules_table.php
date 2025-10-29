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
        Schema::table('bid_schedules', function (Blueprint $table) {
            // 1. ADD the new mop_id column, constrained to the mops table
            $table->foreignId('mop_id')
                ->nullable() // Keep nullable if needed, e.g., during data migration
                ->after('id') // Place it right after the primary key (or adjust as needed)
                ->constrained('mops') // Links to mops.id
                ->cascadeOnDelete(); // Or nullOnDelete(), restrict() depending on desired behavior

            // 2. DROP the old procID column
            // IMPORTANT: If 'procID' had a foreign key constraint, uncomment
            // the following line and use the CORRECT constraint name from your database.
            // Check your schema (e.g., using phpMyAdmin or DBeaver) for the exact name.
            // Example constraint name: 'ntf_bid_schedules_procid_foreign'
            // $table->dropForeign('your_procid_foreign_key_constraint_name_here');

            // Now drop the column itself.
            $table->dropColumn('procID'); // <<-- UNCOMMENTED THIS LINE
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bid_schedules', function (Blueprint $table) {
            // 1. RE-ADD the procID column (ensure type and position match original)
            // Assuming procID was a string and maybe came after 'id'
            $table->string('procID')->nullable()->after('id');

            // Re-add its foreign key constraint IF you dropped it in the up() method.
            // Ensure 'procurements' table and 'procID' column exist for this to work.
            // Example:
            // $table->foreign('procID', 'your_procid_foreign_key_constraint_name_here') // Optional: Give the constraint a name
            //       ->references('procID')
            //       ->on('procurements')
            //       ->cascadeOnDelete(); // Or the original onDelete behavior

            // 2. DROP the mop_id column and its constraint added in up()
            $table->dropForeign(['mop_id']); // Automatically finds constraint named ntf_bid_schedules_mop_id_foreign
            $table->dropColumn('mop_id');
        });
    }
};
