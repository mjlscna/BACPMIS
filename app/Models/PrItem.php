<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrItem extends Model
{
    use HasFactory;

    protected $table = 'pr_items';

    protected $fillable = [
        'procID',
        'prItemID',
        'item_no',
        'description',
        'amount',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procID', 'procID');
    }
}
