<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrLotPrstage extends Model
{
    use HasFactory;

    protected $table = 'pr_lot_prstage';

    protected $fillable = [
        'procID',
        'pr_stage_id',
        'stage_history',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID', 'procID');
    }

    public function procurementStage()
    {
        return $this->belongsTo(ProcurementStage::class, 'pr_stage_id', 'id');
    }

}
