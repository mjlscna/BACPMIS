<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BidSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procID',
        'ib_number',
        'pre_proc_conference',
        'ads_post_ib',
        'pre_bid_conf',
        'eligibility_check',
        'sub_open_bids',
        'bidding_number',
        'bidding_date',
        'bidding_result',
    ];

    // If procID is related to the Procurement model (as foreign key), you can define a relationship:
    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID', 'procID');
    }
}
