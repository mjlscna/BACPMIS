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
            // 1. Add polymorphic columns
            $table->string('procurable_type')->after('status')->nullable();
            $table->string('uid')->after('procurable_type')->nullable(); // This will store the procID or prItemID

            // 2. Add mode_of_procurement_id
            if (!Schema::hasColumn('mop_groups', 'mode_of_procurement_id')) {
                $table->foreignId('mode_of_procurement_id')
                    ->after('uid')
                    ->nullable()
                    ->constrained('mode_of_procurements');
            }

            // 3. Add mode_order. I've made this an integer, as 'string' is incorrect.
            $table->string('mode_order')->after('mode_of_procurement_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mop_groups', function (Blueprint $table) {
            // Drop in reverse order
            $table->dropIndex(['uid', 'procurable_type']);
            $table->dropColumn('mode_order');

            if (Schema::hasColumn('mop_groups', 'mode_of_procurement_id')) {
                $table->dropForeign(['mode_of_procurement_id']);
                $table->dropColumn('mode_of_procurement_id');
            }

            $table->dropColumn('uid');
            $table->dropColumn('procurable_type');
        });
    }
};
