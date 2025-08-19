<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrSvp extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'procID',
        'uid',
        'resolution_number',
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
