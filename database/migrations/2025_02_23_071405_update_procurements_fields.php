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
        Schema::table('procurements', function (Blueprint $table) {
            // Change immediate_date_needed and date_needed from date to string
            $table->string('immediate_date_needed')->nullable()->change();
            $table->string('date_needed')->nullable()->change();

            // Remove category_venue_id to reposition it
            $table->dropForeign(['category_venue_id']);
            $table->dropColumn('category_venue_id');
        });

        Schema::table('procurements', function (Blueprint $table) {
            // Re-add category_venue_id after venue_province_huc_id
            $table->foreignId('category_venue_id')
                ->nullable()
                ->constrained('category_venues')
                ->nullOnDelete()
                ->after('venue_province_huc_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Revert immediate_date_needed and date_needed back to date type
            $table->date('immediate_date_needed')->nullable()->change();
            $table->date('date_needed')->nullable()->change();

            // Remove category_venue_id to reposition it again
            $table->dropForeign(['category_venue_id']);
            $table->dropColumn('category_venue_id');
        });

        Schema::table('procurements', function (Blueprint $table) {
            // Re-add category_venue_id at the end
            $table->foreignId('category_venue_id')
                ->nullable()
                ->constrained('category_venues')
                ->nullOnDelete();
        });
    }
};
