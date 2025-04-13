<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundSource extends Model
{
    use HasFactory;

    protected $fillable = ['fundsources', 'slug', 'is_active'];

    public function procurements()
    {
        return $this->hasMany(Procurement::class);
    }

}
