<?php

// app/Models/Mop.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mop extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurable_id',
        'procurable_type',
        'uid',
        'mode_of_procurement_id',
        'mode_order',
    ];

    /**
     * Get the parent procurable model (could be a Procurement or a PrItem).
     */
    public function procurable(): MorphTo
    {
        return $this->morphTo();
    }

    public function modeOfProcurement()
    {
        return $this->belongsTo(ModeOfProcurement::class, 'mode_of_procurement_id');
    }

    // You can move the relationships for schedules here
    // Note: You'll need to update BidSchedule to belong to Mop instead of using procID
    public function bidSchedules()
    {
        return $this->hasMany(BidSchedule::class); // Assumes BidSchedule has a `mop_id` foreign key
    }
}
