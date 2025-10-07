<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BACApprovedPR extends Model
{
    use HasFactory;
    protected $table = 'bac_approved_prs';
    protected $fillable = [
        'procID',
        'filepath',
        'remarks',
    ];
    public function getRouteKeyName()
    {
        return 'procID';
    }
    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID', 'procID');
    }
}
