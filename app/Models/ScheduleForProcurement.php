<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleForProcurement extends Model
{
    use HasFactory;

    protected $table = 'schedule_for_pr'; // Make sure this matches your migration

    protected $guarded = [];

    // Corrected fillable with snake_case
    protected $fillable = [
        'ib_number',
        'opening_of_bids',
        'project_name',
        'is_framework',
        'status_id',
        'action_taken',
        'next_bidding_schedule',
        'google_drive_link',
        'ABC',
        'two_percent',
        'five_percent'
    ];

    protected $casts = [
        'opening_of_bids' => 'date',
        'is_framework' => 'boolean',
        'next_bidding_schedule' => 'date',
        'abc' => 'decimal:2',
    ];

    public function biddingStatus()
    {
        return $this->belongsTo(BiddingStatus::class, 'status_id');
    }
    public function items()
    {
        return $this->hasMany(ScheduleForProcurementItems::class);
    }
}
