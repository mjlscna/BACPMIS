<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementStage extends Model
{
    use HasFactory;

    protected $fillable = ['procurementstage', 'slug', 'is_active'];

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }
    public function prLotPrstages()
    {
        return $this->hasMany(PrLotPrstage::class, 'pr_stage_id', 'id');
    }

}
