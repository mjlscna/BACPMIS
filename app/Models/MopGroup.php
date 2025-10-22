<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MopGroup extends Model
{
    use HasFactory;
    protected $fillable = [

        'ref_number',
        'status',
        'procurable_type',
        'uid',
        'mode_of_procurement_id',
        'mode_order',
    ];

    public function modeOfProcurement()
    {
        return $this->belongsTo(ModeOfProcurement::class, 'mode_of_procurement_id');
    }
    public function procurements()
    {
        return $this->belongsToMany(Procurement::class, 'mop_group_procurement');
    }

    public function prItems()
    {
        return $this->belongsToMany(PrItem::class, 'mop_group_pr_item', 'mop_group_id', 'pr_item_id');
    }

}
