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
        Schema::table('mop_groups', function (Blueprint $table) {
            // STEP 1: Drop the foreign key constraint first.
            // Replace 'mop_groups_mode_of_procurement_id_foreign' with the actual constraint name
            // if it's different in your database schema.
            $table->dropForeign(['mode_of_procurement_id']); // Laravel convention uses table_column_foreign

            // STEP 2: Now drop the columns.
            $table->dropColumn(['uid', 'mode_of_procurement_id', 'mode_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mop_groups', function (Blueprint $table) {
            // STEP 1: Re-add the columns.
            $table->string('uid')->after('procurable_type')->nullable(); // Adjust position if needed
            $table->unsignedBigInteger('mode_of_procurement_id')->after('uid')->nullable(); // Use correct type if not BigInteger
            $table->unsignedInteger('mode_order')->after('mode_of_procurement_id')->default(0);

            // STEP 2: Re-add the foreign key constraint.
            // Make sure 'mode_of_procurements' table and 'id' column exist.
            $table->foreign('mode_of_procurement_id')
                ->references('id')
                ->on('mode_of_procurements')
                ->nullOnDelete(); // Or cascadeOnDelete(), setNull(), restrict() depending on your needs
        });
    }
};
