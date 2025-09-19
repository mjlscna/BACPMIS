<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrItem extends Model
{
    use HasFactory;

    protected $table = 'pr_items';
    protected $primaryKey = 'prItemID'; // important!

    protected $fillable = [
        'procID',
        'prItemID',   // since you manually assign it
        'item_no',
        'description',
        'amount',
    ];

    public $incrementing = false; // if prItemID is not auto-increment
    protected $keyType = 'string'; // if it's not int (change if it's int)

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID', 'procID');
    }

    public function prstage()
    {
        return $this->hasOne(PrItemPrstage::class, 'prItemID', 'prItemID');
    }

    public function getStageNameAttribute()
    {
        return $this->prstage?->stage?->procurementstage;
    }

}


