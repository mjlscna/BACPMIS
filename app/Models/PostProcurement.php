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
        'bid_evaluation_date',
        'post_qual_date',
        'resolution_number',
        'recommending_for_award',
        'notice_of_award',
        'awarded_amount',
        'date_of_posting_of_award_on_philgeps',
    ];
    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID');
    }

}
