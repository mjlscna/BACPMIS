<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class Procurement extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    // Define string primary key
    protected $primaryKey = 'procID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'procID',
        'pr_number',
        'procurement_type',
        'procurement_program_project',
        'date_receipt',
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

    protected $auditInclude = [
        'procID',
        'pr_number',
        'procurement_type',
        'procurement_program_project',
        'date_receipt',
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

    protected $auditTimestamps = true;

    protected $auditStrict = false;

    public function generateTags(): array
    {
        return ['procurement', $this->pr_number];
    }

    public function getRouteKeyName()
    {
        return 'procID';
    }

    public static function generatePrNumber(bool $isAdvance = false): string
    {
        $year = $isAdvance ? now()->year + 1 : now()->year;

        return DB::transaction(function () use ($year) {
            // Ensure the row exists
            DB::table('pr_counters')->updateOrInsert(
                ['year' => $year],
                ['last_number' => DB::raw('last_number')] // No change if it exists
            );

            // Lock the row for update
            $counter = DB::table('pr_counters')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $next = $counter->last_number + 1;

            DB::table('pr_counters')
                ->where('year', $year)
                ->update(['last_number' => $next]);

            return sprintf('%s-%04d', $year, $next);
        });
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
        return $this->hasOne(PostProcurement::class, 'ref_id', 'procID');
    }

    public function pr_items()
    {
        return $this->hasMany(PrItem::class, 'procID', 'procID');
    }

    public function mopLots()
    {
        return $this->hasMany(MopLot::class, 'procID', 'procID');
    }

    public function mopItems()
    {
        return $this->hasMany(MopItem::class, 'procID', 'procID');
    }

    public function currentPrStage()
    {
        return $this->hasOne(PrLotPrstage::class, 'procID', 'procID')->latest();
    }

    public function prLotPrstages()
    {
        return $this->hasMany(PrLotPrstage::class, 'procID', 'procID');
    }

    public function prItemPrstages()
    {
        return $this->hasMany(PrItemPrstage::class, 'procID', 'procID');
    }

    public function bacApprovedPr()
    {
        return $this->hasOne(BACApprovedPR::class, 'procID', 'procID');
    }

    public function mops(): MorphMany
    {
        return $this->morphMany(Mop::class, 'procurable', 'procurable_type', 'procurable_id', 'procID');
    }

    public function mopGroups()
    {
        return $this->morphToMany(
            MopGroup::class,
            'biddable',
            'mop_group_items',
            'biddable_id',
            'mop_group_id',
            'procID'
        );
    }

    public function scheduleItems()
    {
        return $this->morphMany(ScheduleForProcurementItems::class, 'itemable', 'itemable_type', 'itemable_id', 'procID');
    }

    public function currentLotRemark()
    {
        return $this->hasOne(PrLotRemark::class, 'procID', 'procID')
            ->latest('remark_history');
    }

    public function prLotRemarks()
    {
        return $this->hasMany(PrLotRemark::class, 'procID', 'procID');
    }

    /**
     * A PR may only be converted from Per Lot to Per Item, and only while it is
     * still untouched: lot stage never moved off 1 and the Mode of Procurement is
     * still the default seeded at creation. The reverse direction is never allowed.
     */
    public function canConvertToPerItem(): bool
    {
        if ($this->procurement_type !== 'perLot') {
            return false;
        }

        // Stage must never have moved off 1 (also catches advance-then-revert)
        if ($this->prLotPrstages()->where('pr_stage_id', '!=', 1)->exists()) {
            return false;
        }

        // Per Lot supports multiple modes, so a second row means the MOP page was used
        if ($this->mopLots()->count() > 1) {
            return false;
        }

        return !$this->mopLots()->where('mode_of_procurement_id', '!=', 1)->exists();
    }

    /**
     * Scope the lot-vs-item remark lookup by procurement_type.
     *
     * Converted PRs keep their pr_lot_remark rows as history, so matching on the
     * lot remark without checking the type would surface a Per Item PR under a
     * remark it no longer displays.
     */
    public function scopeFilterByRemark(Builder $query, $remarksId): Builder
    {
        return $query->where(function (Builder $q) use ($remarksId) {
            $q->where(function (Builder $lot) use ($remarksId) {
                $lot->where('procurement_type', 'perLot')
                    ->whereHas('currentLotRemark', fn($s) => $s->where('remarks_id', $remarksId));
            })->orWhere(function (Builder $item) use ($remarksId) {
                $item->where('procurement_type', 'perItem')
                    ->whereHas('pr_items.currentItemRemark', fn($s) => $s->where('remarks_id', $remarksId));
            });
        });
    }
}
