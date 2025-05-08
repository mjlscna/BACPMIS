<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NtfBidSchedule extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'procID',
        'modeproc',
        'ib_number',
        'pre_proc_conference',
        'ads_post_ib',
        'pre_bid_conf',
        'eligibility_check',
        'sub_open_bids',
        'bidding_number',
        'bidding_date',
        'bidding_result',
        'ntf_no',
        'ntf_bidding_date',
        'ntf_bidding_result',
        'rfq_no',
        'canvass_date',
        'date_returned_of_canvass',
        'abstract_of_canvass_date',
    ];
    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID', 'procID');
    }
}
