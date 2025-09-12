<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MopItem extends Model
{
    use HasFactory;

    protected $table = 'mop_item';

    protected $fillable = [
        'procID',
        'prItemID',
        'uid',
        'mode_of_procurement_id',
        'mode_order',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID');
    }

    public function item()
    {
        return $this->belongsTo(PrItem::class, 'prItemID');
    }

    public function modeOfProcurement()
    {
        return $this->belongsTo(ModeOfProcurement::class, 'mode_of_procurement_id');
    }
}
