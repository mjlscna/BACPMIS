<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // 🔴 Drop foreign key columns
            $table->dropForeign(['fund_source_id']);
            $table->dropColumn('fund_source_id');

            $table->dropForeign(['fund_class_id']);
            $table->dropColumn('fund_class_id');

            $table->dropForeign(['remarks_id']);
            $table->dropColumn('remarks_id');

            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');

            $table->dropForeign(['procurement_stage_id']);
            $table->dropColumn('procurement_stage_id');

            // 🟡 Drop and re-add `category_venue` after `venue_province_huc_id`
            $table->dropColumn('category_venue');
        });

        Schema::table('procurements', function (Blueprint $table) {
            $table->string('category_venue')->nullable()->after('venue_province_huc_id');
        });
    }

    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // 🔁 Restore deleted columns
            $table->foreignId('fund_source_id')->nullable()->constrained('fund_sources')->nullOnDelete();
            $table->foreignId('fund_class_id')->nullable()->constrained('fund_classes')->nullOnDelete();
            $table->foreignId('remarks_id')->nullable()->constrained('remarks')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('procurement_stage_id')->nullable()->constrained('procurement_stages')->nullOnDelete();

            // 🔁 Re-add `category_venue` at the end (since original position is unknown)
            $table->dropColumn('category_venue');
            $table->string('category_venue')->nullable();
        });
    }
};

