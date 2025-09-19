<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrItemPrstage extends Model
{
    use HasFactory;

    protected $table = 'pr_item_prstage';
    protected $primaryKey = 'id'; // default, unless you named it differently

    protected $fillable = [
        'procID',
        'prItemID',
        'pr_stage_id',
        'stage_history',
    ];

    public function item()
    {
        return $this->belongsTo(PrItem::class, 'prItemID', 'prItemID');
    }

    public function stage()
    {
        return $this->belongsTo(ProcurementStage::class, 'pr_stage_id', 'id');
    }
}

