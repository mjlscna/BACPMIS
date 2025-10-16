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
        Schema::table('schedule_for_pr', function (Blueprint $table) {
            // 1. Modify the action_taken column to be nullable
            $table->string('action_taken')->nullable()->change();
            $table->unsignedBigInteger('status_id')->nullable()->change();
            // 2. Remove the old columns
            $table->dropColumn(['no_items_lot', 'pr_count']);

            // 3. Add the new columns for percentages (using decimal for currency)
            $table->decimal('two_percent', 15, 2)->nullable()->after('ABC');
            $table->decimal('five_percent', 15, 2)->nullable()->after('two_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_for_pr', function (Blueprint $table) {
            // This perfectly reverses the changes made in the up() method

            // 1. Revert action_taken to not be nullable
            $table->string('action_taken')->nullable(false)->change();
            $table->unsignedBigInteger('status_id')->nullable(false)->change();
            // 2. Add the removed columns back
            $table->integer('no_items_lot')->nullable();
            $table->integer('pr_count')->nullable();

            // 3. Remove the new percentage columns
            $table->dropColumn(['two_percent', 'five_percent']);
        });
    }
};
