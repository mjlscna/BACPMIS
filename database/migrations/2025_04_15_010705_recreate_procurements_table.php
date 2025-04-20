<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('procurements');

        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->string('procID')->unique();
            $table->string('pr_number')->unique();
            $table->string('procurement_program_project');
            $table->date('date_receipt_advance')->nullable();
            $table->date('date_receipt_signed')->nullable();
            $table->enum('rbac_sbac', ['RBAC', 'SBAC'])->default('RBAC');
            $table->string('dtrack_no')->nullable();
            $table->string('unicode')->nullable();
            $table->foreignId('divisions_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('cluster_committees_id')->nullable()->constrained('cluster_committees')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('venue_specific_id')->nullable()->constrained('venues')->nullOnDelete();
            $table->foreignId('venue_province_huc_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->string('category_venue')->nullable();
            $table->string('approved_ppmp');
            $table->string('app_updated');
            $table->date('immediate_date_needed')->nullable();
            $table->date('date_needed')->nullable();
            $table->string('pmo_end_user')->nullable();
            $table->boolean('early_procurement')->default(false);
            $table->foreignId('fund_source_id')->nullable()->constrained('fund_sources')->nullOnDelete();
            $table->string('expense_class')->nullable();
            $table->decimal('abc', 15, 2)->nullable();
            $table->enum('abc_50k', ['above_50k', '50k_or_less'])->default('50k_or_less');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};

