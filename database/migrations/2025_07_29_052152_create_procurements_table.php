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
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->string('procID')->unique();
            $table->string('pr_number')->nullable();
            $table->text('procurement_program_project')->nullable();
            $table->date('date_receipt_advance')->nullable();
            $table->date('date_receipt_signed')->nullable();
            $table->string('rbac_sbac')->nullable();
            $table->string('dtrack_no')->nullable();
            $table->string('unicode')->nullable();

            // Foreign keys
            $table->foreignId('divisions_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('cluster_committees_id')->nullable()->constrained('cluster_committees')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('venue_specific_id')->nullable()->constrained('venue_specifics')->nullOnDelete();
            $table->foreignId('venue_province_huc_id')->nullable()->constrained('province_hucs')->nullOnDelete();
            $table->foreignId('end_users_id')->nullable()->constrained('end_users')->nullOnDelete();
            $table->foreignId('fund_source_id')->nullable()->constrained('fund_sources')->nullOnDelete();
            $table->foreignId('fund_class_id')->nullable()->constrained('fund_classes')->nullOnDelete();
            $table->foreignId('remarks_id')->nullable()->constrained('remarks')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('mode_of_procurement_id')->nullable()->constrained('mode_of_procurements')->nullOnDelete();
            $table->foreignId('procurement_stage_id')->nullable()->constrained('procurement_stages')->nullOnDelete();
            $table->foreignId('category_venue_id')->nullable()->constrained('category_venues')->nullOnDelete();

            $table->boolean('approved_ppmp')->nullable();
            $table->boolean('app_updated')->nullable();
            $table->date('immediate_date_needed')->nullable();
            $table->date('date_needed')->nullable();

            $table->boolean('early_procurement')->nullable();
            $table->string('expense_class')->nullable();
            $table->decimal('abc', 15, 2)->nullable();
            $table->boolean('abc_50k')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
