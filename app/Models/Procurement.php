<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procurement extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $fillable = [
        'procID',
        'pr_number',
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
        'category_venue',
        'approved_ppmp',
        'app_updated',
        'immediate_date_needed',
        'date_needed',
        'pmo_end_user',
        'early_procurement',
        'fund_source_id',
        'expense_class',
        'abc',
        'abc_50k'

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
    public function bidModeOfProcurement()
    {
        return $this->hasMany(BidModeOfProcurement::class, 'procID', 'procID');
    }
    public function bidSchedules()
    {
        return $this->hasMany(BidSchedule::class, 'procID', 'procID');
    }

}
