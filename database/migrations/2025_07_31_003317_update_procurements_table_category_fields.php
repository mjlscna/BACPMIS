<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // 1. Drop foreign key and column category_venue_id
            $table->dropForeign(['category_venue_id']);
            $table->dropColumn('category_venue_id');

            // 2. Add category_venue as string
            $table->string('category_venue')->nullable()->after('procurement_stage_id');

            // 3. Add category_type_id (foreign key)
            $table->foreignId('category_type_id')->nullable()
                ->after('category_id')
                ->constrained('category_types')
                ->nullOnDelete();

            // 4. Move rbac_sbac after category_type_id
            $table->string('rbac_sbac')->nullable()->after('category_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // 1. Drop new columns if they exist
            if (Schema::hasColumn('procurements', 'category_venue')) {
                $table->dropColumn('category_venue');
            }

            if (Schema::hasColumn('procurements', 'category_type_id')) {
                $table->dropForeign(['category_type_id']);
                $table->dropColumn('category_type_id');
            }

            if (Schema::hasColumn('procurements', 'rbac_sbac')) {
                $table->dropColumn('rbac_sbac');
            }

            // 2. Restore original foreign key
            if (!Schema::hasColumn('procurements', 'category_venue_id')) {
                $table->foreignId('category_venue_id')->nullable()
                    ->constrained('category_venues')
                    ->nullOnDelete()
                    ->after('procurement_stage_id');
            }

            // 3. Restore rbac_sbac to original position (if needed)
            if (!Schema::hasColumn('procurements', 'rbac_sbac')) {
                $table->string('rbac_sbac')->nullable()->after('dtrack_no');
            }
        });
    }

};
