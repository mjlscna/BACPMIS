<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeOfProcurement extends Model
{
    use HasFactory;

    protected $fillable = ['modeofprocurements', 'slug', 'is_active'];

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }
    public function bidModeOfProcurements()
    {
        return $this->belongsTo(BidModeOfProcurement::class, 'bid_mode_of_procurement_id');
    }
}
