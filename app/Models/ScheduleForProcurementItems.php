<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleForProcurementItems extends Model
{
    use HasFactory;
    protected $table = 'schedule_procurement_items';
    protected $fillable = ['schedule_for_procurement_id', 'itemable_type', 'itemable_UID'];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ScheduleForProcurement::class);
    }
    public function itemable()
    {
        return $this->morphTo();
    }
}
