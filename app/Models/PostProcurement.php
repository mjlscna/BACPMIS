<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostProcurement extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $fillable = [
        'procID',
        'resolution_number',
        'bid_evaluation_date',
        'post_qual_date',
        'recommending_for_award',
        'notice_of_award',
        'philgeps_reference_no',
        'award_notice_no',
        'awarded_amount',
        'date_of_posting_of_award_on_philgeps',
        'supplier_id',
        'procurement_stage_id',
        'remarks_id',
    ];
    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID');
    }

}
