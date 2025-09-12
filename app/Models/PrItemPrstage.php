<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrItemPrstage extends Model
{
    use HasFactory;

    protected $table = 'pr_item_prstage';

    protected $fillable = [
        'procID',
        'prItemID',
        'pr_stage_id',
        'stage_history',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID');
    }

    public function item()
    {
        return $this->belongsTo(PrItem::class, 'prItemID');
    }

    public function stage()
    {
        return $this->belongsTo(ProcurementStage::class, 'pr_stage_id');
    }
}
