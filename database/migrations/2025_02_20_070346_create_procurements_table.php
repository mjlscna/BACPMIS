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
            $table->string('pr_number')->unique();
            $table->string('ib_number')->nullable();
            $table->string('np_no')->nullable();
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
            $table->boolean('approved_ppmp')->default(false);
            $table->boolean('app_updated')->default(false);
            $table->date('immediate_date_needed')->nullable();
            $table->date('date_needed')->nullable();
            $table->string('pmo_end_user')->nullable();
            $table->boolean('early_procurement')->default(false);
            $table->foreignId('fund_source_id')->nullable()->constrained('fund_sources')->nullOnDelete();
            $table->string('expense_class')->nullable();
            $table->decimal('abc', 15, 2)->nullable();
            $table->foreignId('mode_of_procurement_id')->nullable()->constrained('mode_of_procurements')->nullOnDelete();
            $table->enum('abc_50k', ['above_50k', '50k_or_less'])->default('50k_or_less');
            $table->date('pre_proc_conference')->nullable();
            $table->date('ads_post_ib')->nullable();
            $table->date('pre_bid_conf')->nullable();
            $table->date('eligibility_check')->nullable();
            $table->date('sub_open_bids')->nullable();
            $table->date('first_bidding_date')->nullable();
            $table->enum('first_bidding_result', ['Successful', 'Failed', 'Not Applicable'])->nullable();
            $table->date('second_bidding_date')->nullable();
            $table->enum('second_bidding_result', ['Successful', 'Failed', 'Not Applicable'])->nullable();
            $table->date('bid_evaluation_date')->nullable();
            $table->date('post_qual_date')->nullable();
            $table->string('resolution_number')->nullable();
            $table->string('ntf_no')->nullable();
            $table->date('ntf_bidding_date')->nullable();
            $table->string('ntf_bidding_result')->nullable();
            $table->string('rfq_no')->nullable();
            $table->date('canvass_date')->nullable();
            $table->date('returned_canvass_date')->nullable();
            $table->date('abstract_of_canvass_date')->nullable();
            $table->date('bac_resolution_award_date')->nullable();
            $table->date('notice_of_award_date')->nullable();
            $table->decimal('awarded_amount', 15, 2)->nullable();
            $table->string('award_notice_number')->nullable();
            $table->date('posting_award_philgeps')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('procurement_stage_id')->nullable()->constrained('procurement_stages')->nullOnDelete();
            $table->foreignId('remarks_id')->nullable()->constrained('remarks')->nullOnDelete();
            $table->string('reschedule_cancellation_letter')->nullable();
            $table->date('forwarded_to_pmu_date')->nullable();
            $table->string('philgeps_posting_reference_number')->nullable();
            $table->decimal('contract_amount', 15, 2)->nullable();
            $table->string('purchase_order_contract_number')->nullable();
            $table->date('contract_signing_po')->nullable();
            $table->date('notice_to_proceed')->nullable();
            $table->text('delivery_completion')->nullable();
            $table->text('inspection_acceptance')->nullable();
            $table->text('list_of_invited_observers')->nullable();
            $table->date('pre_bid_conf2')->nullable();
            $table->date('eligibility_check3')->nullable();
            $table->date('sub_open_bids4')->nullable();
            $table->date('bid_evaluation5')->nullable();
            $table->date('post_qual6')->nullable();
            $table->text('delivery_completion_acceptance')->nullable();
            $table->text('remarks_changes_app')->nullable();
            $table->dateTime('date_last_updated')->nullable();
            $table->enum('bac_type', ['BAC A', 'Alternative'])->nullable();
            $table->boolean('po')->default(false);
            $table->foreignId('fund_class_id')->nullable()->constrained('fund_classes')->nullOnDelete();
            $table->boolean('pr_stat')->default(false);
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
