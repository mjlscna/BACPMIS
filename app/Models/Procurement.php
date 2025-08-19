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
        'dtrack_no',
        'unicode',
        'divisions_id',
        'cluster_committees_id',
        'category_id',
        'category_type_id',
        'bac_type_id',
        'venue_specific_id',
        'venue_province_huc_id',
        'category_venue',
        'approved_ppmp',
        'app_updated',
        'immediate_date_needed',
        'date_needed',
        'end_users_id',
        'early_procurement',
        'fund_source_id',
        'expense_class',
        'abc',
        'abc_50k'

    ];
    public static function generatePrNumber(bool $isEarly = false): string
    {
        $year = $isEarly ? now()->year + 1 : now()->year;

        $count = self::whereYear('created_at', $year)->count() + 1;

        return sprintf('%s-%04d', $year, $count);
    }

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
    public function categoryType()
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function bacType()
    {
        return $this->belongsTo(BacType::class, 'bac_type_id');
    }

    public function venueSpecific()
    {
        return $this->belongsTo(VenueSpecific::class, 'venue_specific_id');
    }

    public function venueProvincesHUC()
    {
        return $this->belongsTo(ProvinceHuc::class, 'venue_province_huc_id');
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
    public function postProcurement()
    {
        return $this->hasOne(PostProcurement::class, 'procID');
    }

}
