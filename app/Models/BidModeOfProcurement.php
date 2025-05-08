<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BidModeOfProcurement extends Model
{
    use SoftDeletes;

    protected $table = 'bid_mode_of_procurement';

    protected $fillable = [
        'procID',
        'uid',
        'mode_of_procurement_id',
        'mode_order',
    ];

    /**
     * Define the relationship with the ModeOfProcurement model
     */
    public function modeOfProcurement()
    {
        return $this->belongsTo(ModeOfProcurement::class, 'mode_of_procurement_id');
    }

    /**
     * Define the relationship with the Procurement model (procID)
     */
    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID'); // Assuming 'procID' is the foreign key
    }
}
