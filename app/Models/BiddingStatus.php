<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiddingStatus extends Model
{
    use HasFactory;
    protected $table = 'bidding_status';
    protected $fillable = [
        'name',
    ];

}
