<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Remove old columns
            $table->dropColumn(['date_receipt_advance', 'date_receipt_signed']);

            // Add new column
            $table->boolean('perItem')->default(false)->after('pr_number');

            // Change existing columns to boolean with default false
            $table->boolean('approved_ppmp')->default(false)->change();
            $table->boolean('app_updated')->default(false)->change();

            // Add new column
            $table->date('date_receipt')->nullable()->after('procurement_program_project');
        });
    }

    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Rollback new columns
            $table->dropColumn(['perItem', 'date_receipt']);

            // Restore removed columns
            $table->date('date_receipt_advance')->nullable()->after('procurement_program_project');
            $table->date('date_receipt_signed')->nullable()->after('date_receipt_advance');

            // Revert existing columns (assuming they were string before)
            $table->string('approved_ppmp')->nullable()->change();
            $table->string('app_updated')->nullable()->change();
        });
    }
};
