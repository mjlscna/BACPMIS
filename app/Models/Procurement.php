<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number',
        'ib_number',
        'np_no',
        'procurement_program_project',
        'date_receipt_advance',
        'date_receipt_signed',
        'rbac_sbac',
        'dtrack_no',
        'unicode',
        'divisions_id',
        'cluster_committees_id',
        'category_id',
        'venue_specific_id',
        'venue_province_huc_id',
        'category_venue_id',
        'approved_ppmp',
        'app_updated',
        'immediate_date_needed',
        'date_needed',
        'pmo_end_user',
        'early_procurement',
        'fund_source_id',
        'expense_class',
        'abc',
        'mode_of_procurement_id',
        'abc_50k',
        'pre_proc_conference',
        'ads_post_ib',
        'pre_bid_conf',
        'eligibility_check',
        'sub_open_bids',
        'first_bidding_date',
        'first_bidding_result',
        'second_bidding_date',
        'second_bidding_result',
        'bid_evaluation_date',
        'post_qual_date',
        'resolution_number',
        'ntf_no',
        'ntf_bidding_date',
        'ntf_bidding_result',
        'rfq_no',
        'canvass_date',
        'returned_canvass_date',
        'abstract_of_canvass_date',
        'bac_resolution_award_date',
        'notice_of_award_date',
        'awarded_amount',
        'award_notice_number',
        'posting_award_philgeps',
        'supplier_id',
        'procurement_stage_id',
        'remarks_id',
        'remarks_note',//add ''
        'reschedule_cancellation_letter',
        'forwarded_to_pmu_date',
        'philgeps_posting_reference_number',
        'contract_amount',
        'purchase_order_contract_number',
        'contract_signing_po',
        'notice_to_proceed',
        'delivery_completion',
        'inspection_acceptance',
        'list_of_invited_observers',
        'pre_bid_conf2',
        'eligibility_check3',
        'sub_open_bids4',
        'bid_evaluation5',
        'post_qual6',
        'delivery_completion_acceptance',
        'remarks_changes_app',
        'date_last_updated',
        'bac_type',
        'po',
        'fund_class_id',
        'pr_stat',
        'category_venue_id',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'divisions_id');
    }

    public function clusterCommittee()
    {
        return $this->belongsTo(ClusterCommittee::class, 'cluster_committees_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function venueSpecific()
    {
        return $this->belongsTo(Venue::class, 'venue_specific_id');
    }

    public function venueProvince()
    {
        return $this->belongsTo(Province::class, 'venue_province_huc_id');
    }

    public function fundSource()
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id');
    }

    public function modeOfProcurement()
    {
        return $this->belongsTo(ModeOfProcurement::class, 'mode_of_procurement_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function procurementStage()
    {
        return $this->belongsTo(ProcurementStage::class, 'procurement_stage_id');
    }

    public function remarks()
    {
        return $this->belongsTo(Remarks::class, 'remarks_id');
    }

    public function fundClass()
    {
        return $this->belongsTo(FundClass::class, 'fund_class_id');
    }

    public function categoryVenue()
    {
        return $this->belongsTo(CategoryVenue::class, 'category_venue_id');
    }

    public function endUser()
    {
        return $this->belongsTo(EndUser::class, 'end_users_id');
    }
}
